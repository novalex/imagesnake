#!/usr/bin/python3

import re
import sys
import ssl
import time
import json
import ntpath
import importlib
from random import randrange
from math import floor
from urllib.request import urlopen
from urllib.parse import urljoin
from bs4 import BeautifulSoup
from PIL import Image, ImageStat
from multiprocessing.dummy import Pool as ThreadPool
from luminosity import get_brightness


def get_filename(path):
    head, tail = ntpath.split(path)
    return tail or ntpath.basename(head)


i = 0

def process_image(image_tag):
    name = get_filename(image_tag['src'])
    global i, image_nr
    i = i + 1
    complete = floor(100.0 * i / image_nr)
    print(f'[STEP] Getting data for image {name:s}\n[PROGRESS] {complete:d}')

    # Force absolute URLs.
    image_tag['src'] = urljoin(siteurl, image_tag['src'])
    # Get image size.
    image = urlopen(image_tag['src'], context=sslctx)
    image_res = Image.open(image)

    return {
        'name': name,
        'src': image_tag['src'],
        'alt': image_tag['alt'],
        'width': image_res.size[0],
        'height': image_res.size[1],
		'format': image_res.format,
		'size': image.headers['content-length'],
        'brightness': get_brightness(image_res)
    }


if ( len(sys.argv) == 1 ):
	print('[ERROR] No URL provided.')
	raise SystemExit(0)

# Scrape images from URL.
siteurl = sys.argv[1]
sslctx = ssl._create_unverified_context()
html = urlopen(siteurl, context=sslctx)
bs = BeautifulSoup(html, 'html.parser')
image_tags = bs.find_all('img', {
	'src': re.compile('.jpe?g|png|gif')
})
image_nr = len(image_tags)

# Start processing.
print(f'[START] Fetching data for {image_nr:d} images...')
pool = ThreadPool(4)
images = pool.map(process_image, image_tags)
# Output JSON.
print(f'[JSON] {json.dumps(images):s}')
