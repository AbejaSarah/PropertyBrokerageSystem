from flask import Blueprint, render_template, request, redirect, url_for
from werkzeug.security import generate_password_hash

from models.user import User
from utils.auth_utils import login_required

signup_bp = Blueprint('signup', __name__)

@signup_bp.route('/signup', methods=['GET', 'POST'])
def signup():
    if request.method == 'POST':
        # Get form data
        name = request.form['name']
        email = request.form['email']
        password = request.form['password']

        # Check if user already exists
        existing_user = User.get_by_email(email)
        if existing_user:
            return render_template('signup.html', error='User already exists')

        # Hash password
        hashed_password = generate_password_hash(password)

        # Create new user
        new_user = User(name=name, email=email, password=hashed_password)
        new_user.save()

        # Redirect user to login page
        return redirect(url_for('login.login'))

    # If request method is GET, render signup page
    return render_template('signup.html')

