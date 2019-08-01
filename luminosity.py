from PIL import Image
import sys


def get_brightness(image):
    total_brightness = 0
    total_pixels = 0

    for pixel in get_pixels(image):
        brightness = get_pixel_brightness(pixel)
        total_brightness += brightness
        total_pixels += 1

    if total_pixels > 0:
        average_brightness = total_brightness / total_pixels
        return round(average_brightness, 3)

    else:
        return 0


def get_pixels(image):
    """Get all non-transparent pixels."""

    image = image.convert('RGBA')
    pixel_matrix = image.load()
    size = image.size

    width, height = size

    for x in range(0, width):
        for y in range(0, height):
            pixel = pixel_matrix[x, y]
            if not is_transparent(pixel):
                yield pixel[:3]


def get_pixel_brightness(pixel):
    red, green, blue = pixel

    redness = red * 0.2126
    greenness = green * 0.7152
    blueness = blue * 0.0722

    brightness = redness + greenness + blueness

    return brightness


def is_transparent(pixel):
    if len(pixel) <= 3:
        return False
    else:
        if pixel[3] > 0:
            return False
    return True


def get_brightest(files):
    brightest = None

    for path in files:
        try:
            brightness = get_brightness(path)
        except IOError:
            continue
        entry = (brightness, path)

        if entry > brightest:
            brightest = entry

    return brightest
