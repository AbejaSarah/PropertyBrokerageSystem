from scraper_utils import get_soup, get_links_from_class, extract_data_from_property_page
from db import DB

class Scraper:
    def __init__(self):
        self.db = DB()

    def get_properties(self):
        """
        Scrape property data from Rightmove
        """
        search_url = "https://www.rightmove.co.uk/property-to-rent/find.html?locationIdentifier=REGION%5E87490&propertyTypes=&includeLetAgreed=false&mustHave=&dontShow=&furnishTypes=&keywords="
        page_number = 0
        while True:
            page_number += 1
            print(f"Scraping page {page_number}")
            soup = get_soup(f"{search_url}&index={page_number*24}")
            property_links = get_links_from_class(soup, "propertyCard-img-link")
            if not property_links:
                break
            for link in property_links:
                data = extract_data_from_property_page(get_soup(link))
                self.db.save_property(data)
