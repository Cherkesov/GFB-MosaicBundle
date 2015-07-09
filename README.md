# GFB-MosaicBundle

Bundle for Symfony2

## Features

1. Fill base of images which will be used in a paving
2. Create mosaic for selected image with collected base

## Usage

1. Run command

    ```
    php app/console gfb:mosaic:fill:google --query="girls+hairstyles"
    ```
    
    and wait for base is filled with images. Run command again with other keywords (param "query") if you want to expand your images base.

2. Run command 

    ```
    php app/console gfb:mosaic:create --file="4.jpg" --size=32 --level=2 --accuracy=16 --opacity=0.6
    ```
    
    Options:
      - **file** which will be processed, must located in **web/mosaic/images/4.jpg**
      - **size** of biggest segment size of mosaic
      - **level** specify count of segments divisions. If size=32 and level=4 it means that mosaic will contains segments with sizes 32x32, 16x16, 8x8 and 4x4
      - **accuracy** specify accuracy of color comparison. Zero - average part and segment colors must be equals
      - **opacity** affects the parts. Value between 0 (invisible) and 1 (good opacity)

3. Open http//your-site.com/mosaic/res/R4.jpg.html - this is result file which contains mosaic