import requests
from bs4 import BeautifulSoup


def fetch_html(url):
    """Fetches the HTML content of a given URL."""
    response = requests.get(url)
    if response.status_code == 200:
        return response.text
    else:
        raise Exception(f"Failed to fetch HTML from URL: {url}")


def extract_property_links(html):
    """Extracts the property links from a Rightmove search results page."""
    soup = BeautifulSoup(html, "html.parser")
    property_links = []
    for link in soup.find_all("a", {"class": "propertyCard-link"}):
        property_links.append(link.get("href"))
    return property_links


def extract_property_data(html):
    """Extracts the property data from a Rightmove property page."""
    soup = BeautifulSoup(html, "html.parser")
    property_data = {}

    # Extract the property features
    features = soup.find_all("div", {"class": "dp-features"})
    if len(features) > 0:
        features = features[0].find_all("div", {"class": "ui-property-summary__feature"})
        for feature in features:
            key = feature.find("span", {"class": "ui-property-summary__feature-label"}).text.strip()
            value = feature.find("span", {"class": "ui-property-summary__feature-value"}).text.strip()
            property_data[key] = value

    # Extract the property price
    price = soup.find("div", {"class": "dp-price-wrapper"})
    if price is not None:
        property_data["Price"] = price.find("p", {"class": "dp-price"}).text.strip()

    # Extract the property description
    description = soup.find("div", {"class": "dp-description__text"})
    if description is not None:
        property_data["Description"] = description.text.strip()

    return property_data
