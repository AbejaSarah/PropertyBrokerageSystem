from flask import Flask, render_template, request, redirect, url_for, flash, jsonify
from flask_login import LoginManager, login_user, logout_user, login_required, UserMixin
from werkzeug.security import generate_password_hash, check_password_hash
from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField, SelectField
from wtforms.validators import DataRequired
import os
import json
import pymysql
import pandas as pd
from math import ceil, isnan
import random

app = Flask(__name__)
app.secret_key = os.urandom(24)

# Database configuration
DB_HOST = os.environ.get('DB_HOST', 'localhost')
DB_PORT = int(os.environ.get('DB_PORT', 3306))
DB_USER = os.environ.get('DB_USER', 'root')
DB_PASSWORD = os.environ.get('DB_PASSWORD', '')
DB_NAME = os.environ.get('DB_NAME', 'scrapping_pbs')

# Initialize Flask-Login
login_manager = LoginManager(app)
login_manager.login_view = 'login'

# Configure MySQL connection
conn = None

# User model
class User(UserMixin):
    def __init__(self, user_id, username, password):
        self.id = user_id
        self.username = username
        self.password = password

    @staticmethod
    def get(user_id):
        with get_db_connection() as cursor:
            sql = "SELECT * FROM users WHERE id = %s"
            cursor.execute(sql, (user_id,))
            result = cursor.fetchone()
            if result:
                return User(result['id'], result['username'], result['password'])
            else:
                return None

    @staticmethod
    def get_by_username(username):
        with get_db_connection() as cursor:
            sql = "SELECT * FROM users WHERE username = %s"
            cursor.execute(sql, (username,))
            result = cursor.fetchone()
            if result:
                return User(result['id'], result['username'], result['password'])
            else:
                return None

# Login callback
@login_manager.user_loader
def load_user(user_id):
    return User.get(user_id)

# Database connection management
def get_db_connection():
    global conn
    if conn is None or not conn.open:
        conn = pymysql.connect(
            host=DB_HOST,
            port=DB_PORT,
            user=DB_USER,
            password=DB_PASSWORD,
            db=DB_NAME,
            charset='utf8mb4',
            cursorclass=pymysql.cursors.DictCursor
        )
    return conn.cursor()

# Registration form
class RegistrationForm(FlaskForm):
    username = StringField('Username', validators=[DataRequired()])
    password = PasswordField('Password', validators=[DataRequired()])
    confirm_password = PasswordField('Confirm Password', validators=[DataRequired()])
    sex = SelectField('Sex', choices=[('M', 'Male'), ('F', 'Female')], validators=[DataRequired()])
    address = StringField('Address', validators=[DataRequired()])
    email = StringField('Email', validators=[DataRequired()])

# Login form
class LoginForm(FlaskForm):
    username = StringField('Username', validators=[DataRequired()])
    password = PasswordField('Password', validators=[DataRequired()])

# Load the data
properties = pd.read_csv('data/property.csv')
user_activity = pd.read_csv('data/user_activity.csv')

# Calculate property popularity
property_frequency = user_activity['item_id'].value_counts().to_dict()

@app.route('/')
@app.route('/')
def index():
    # Find the most visited properties
    property_visits = user_activity['item_id'].value_counts().reset_index()
    property_visits.columns = ['item_id', 'visit_count']
    most_visited_properties = property_visits.merge(properties, on='item_id')

    if most_visited_properties.empty:
        no_properties_message = "No properties found."
        return render_template('home.html', no_properties_message=no_properties_message)
    
    return render_template('home.html', properties=most_visited_properties)


@app.route('/login', methods=['GET', 'POST'])
def login():
    form = LoginForm()
    if form.validate_on_submit():
        username = form.username.data
        password = form.password.data
        user = User.get_by_username(username)
        if user and check_password_hash(user.password, password):
            login_user(user)
            flash('Logged in successfully!', 'success')
            return redirect(url_for('index'))
        else:
            flash('Invalid username or password', 'error')
    return render_template('login.html', form=form)

@app.route('/user/register', methods=['GET', 'POST'])
def register():
    form = RegistrationForm()
    if form.validate_on_submit():
        username = form.username.data
        password = form.password.data
        confirm_password = form.confirm_password.data
        sex = form.sex.data
        address = form.address.data
        email = form.email.data

        if password != confirm_password:
            flash('Passwords do not match', 'error')
        else:
            hashed_password = generate_password_hash(password)
            with get_db_connection() as cursor:
                sql = "INSERT INTO users (username, password, sex, address, email) VALUES (%s, %s, %s, %s, %s)"
                cursor.execute(sql, (username, hashed_password, sex, address, email))
                conn.commit()
            flash('Registration successful! You can now log in.', 'success')
            return redirect(url_for('login'))
    return render_template('register.html', form=form)

@app.route('/logout')
@login_required
def logout():
    logout_user()
    flash('Logged out successfully!', 'success')
    return redirect(url_for('index'))

# ...

@app.route('/recommendations', methods=['GET', 'POST'])
def recommendations():
    keywords = str(request.args.get('keywords', ''))
    location = str(request.args.get('location', ''))

    # Convert monthly_rent column to string type
    properties['has_elevator'] = properties['has_elevator'].astype(str)
    properties['room_qty'] = properties['room_qty'].astype(str)

    # Filter the dataset based on user input
    filtered_properties = properties[
        properties['has_elevator'].str.contains(keywords, case=False, na=False) &
        properties['room_qty'].str.contains(location, case=False, na=False)
    ]

    # Generate recommendations based on user input and popularity
    recommendations = filtered_properties[
        filtered_properties['item_id'].isin(property_frequency.keys())
    ].sort_values(by='item_id', ascending=False)

    if recommendations.empty:
        error_message = "No recommendations found for the given criteria."
        return render_template('no_recommendation.html', error_message=error_message)

    # Pagination
    items_per_page = random.randint(10, 900000000)  # Number of recommendations per page
    total_pages = ceil(len(recommendations) / items_per_page)
    page = int(request.args.get('page', 1))
    start_index = (page - 1) * items_per_page
    end_index = start_index + items_per_page
    paginated_recommendations = recommendations[start_index:end_index]

    image_urls = {}

    try:
        results = paginated_recommendations.to_dict('records')
        for result in results:
            if 'imageurl' in result:
                image_url = result['imageurl']
                if image_url:
                    image_urls[image_url] = image_url
    except KeyError as e:
        # Handle missing 'imageurl' key in the result
        print(f"Error: Missing 'imageurl' key in result - {e}")

    # Add image URLs to the recommendations dataframe
    try:
        paginated_recommendations['imageurl'] = paginated_recommendations['property_age'].map(image_urls)
    except KeyError as e:
        # Handle missing 'property_age' key in the paginated_recommendations DataFrame
        print(f"Error: Missing 'property_age' column in paginated_recommendations - {e}")

    # Replace missing image URLs with a default URL
    default_image_url = 'https://media.rightmove.co.uk:443/dir/crop/10:9-16:9/105k/104704/129326042/104704_BRC_BRT_LFSYCL_300_456168009_IMG_00_0000_max_476x317.jpeg'
    paginated_recommendations['imageurl'].fillna(default_image_url, inplace=True)

    num_recommendations = paginated_recommendations.shape[0]
    return render_template('recommendations.html', recommendations=paginated_recommendations, page=page, total_pages=total_pages, num_recommendations=num_recommendations)

# ...

def is_nan(value):
    return isinstance(value, float) and isnan(value)

app.jinja_env.filters['isnan'] = is_nan

@app.route('/data/web_url.json')
def get_web_url_data():
    with open('data/web_url.json') as file:
        data = json.load(file)
    return jsonify(data)

if __name__ == '__main__':
    app.run(debug=True)
