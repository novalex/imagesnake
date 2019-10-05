# ImageSnake
Python image scraper with CLI and web interface.

Given a URL, will fetch all images present on that page and get the name, alt text, dimensions, format, size and brightness value for each one. After all images are fetched, returns JSON string containing all image information. If run from the PHP interface, will display the images in a grid along with relevant information.

![alt text](https://novalx.com/assets/imagesnake-preview.png)

## Installation
**Dependencies**: Python 3, BeautifulSoup, Pillow.
1. Clone the repo
2. Install Python 3
3. Install BeautifulSoup 4, e.g. `sudo apt-get install python3-bs4`
4. Install Pip, e.g. `sudo apt install python3-pip`
5. Install Pillow, e.g. `pip install Pillow`

## Usage
1. Navigate to root directory (where "scraper.py" resides) and open a terminal window there.  
2. Run the scraper using the CLI: `python3 scraper.py "https://example.com"`  
   OR  
   Run from the web interface by accessing the "index.php" file (needs a PHP server to work)  
   You can start a local PHP server by running `php -S localhost:8000` in the root directory
