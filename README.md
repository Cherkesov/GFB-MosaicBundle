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
    php app/console gfb:mosaic:create 1.jpg 128 72
    ```
    
    where:
      - 1.jpg is name of image file in "YOUR_PROJECT_DIR\web\mosaic\images" directory
      - 128 is size of the biggest path for segmentation
      - 72 is number which define accuracy for selecting images for segment (color agreement accuracy)

3. Check dir "YOUR_PROJECT_DIR\web\mosaic\res" and find file R1.jpg - this is result file which contains mosaic