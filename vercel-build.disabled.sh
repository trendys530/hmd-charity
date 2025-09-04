#!/bin/bash

# Create public directory if it doesn't exist
mkdir -p public

# Copy all files to public directory
echo "Copying files to public directory..."
cp -r css/ public/ 2>/dev/null || :
cp -r js/ public/ 2>/dev/null || :
cp -r images/ public/ 2>/dev/null || :
cp -r includes/ public/ 2>/dev/null || :
cp -r php/ public/ 2>/dev/null || :
cp .htaccess public/ 2>/dev/null || :
cp *.html public/ 2>/dev/null || :

# Create a simple index.php if it doesn't exist
if [ ! -f "public/index.php" ]; then
    echo "<?php header('Location: /index.html'); ?>" > public/index.php
fi

echo "Build completed successfully!"
