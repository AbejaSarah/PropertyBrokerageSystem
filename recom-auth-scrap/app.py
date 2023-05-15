from flask import Flask, render_template, request
from recommendation import get_recommendations

app = Flask(__name__)

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/recommend', methods=['POST'])
def recommend():
    bedrooms = int(request.form['bedrooms'])
    bathrooms = int(request.form['bathrooms'])
    price = int(request.form['price'])
    location = request.form['location']
    recommendations = get_recommendations(bedrooms, bathrooms, price, location)
    return render_template('recommendations.html', recommendations=recommendations)

if __name__ == '__main__':
    app.run(debug=True)
