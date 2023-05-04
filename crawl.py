import requests
from bs4 import BeautifulSoup
import csv

# Define the URL of the website to crawl
url = 'https://www.rightmove.co.uk/property-for-sale/find.html?searchType=SALE&locationIdentifier=REGION%5E93922&insId=1&radius=0.0&minPrice=&maxPrice=500000&minBedrooms=2&displayPropertyType=&maxDaysSinceAdded=&sortByPriceDescending=&_includeSSTC=on&primaryDisplayPropertyType=&secondaryDisplayPropertyType=&oldDisplayPropertyType=&oldPrimaryDisplayPropertyType=&letType=&letFurnishType=&houseFlatShare='

# Send a GET request to the website to obtain the HTML content of the page
response = requests.get(url)
html_content = response.content

# Parse the HTML content using Beautiful Soup to extract the desired data elements from the page
soup = BeautifulSoup(html_content, 'html.parser')
properties = soup.find_all('div', class_='l-searchResult')

# Open a CSV file to write the extracted data
with open('properties.csv', mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)

    # Write the header row to the CSV file
    writer.writerow(['Address', 'Price', 'URL'])

    # Loop through the extracted properties and write each property to the CSV file
    for property in properties:
        # Extract the property details from the HTML content
        address = property.find('address', class_='propertyCard-address').text.strip()
        price = property.find('div', class_='propertyCard-priceValue').text.strip()
        url = 'https://www.rightmove.co.uk' + property.find('a', class_='propertyCard-link').get('href')

        # Write the property details to the CSV file
        writer.writerow([address, price, url])

# Print a message to indicate that the data has been successfully extracted and saved to the CSV file
print('Data has been successfully extracted and saved to properties.csv')
