// This script will be used by Vercel to build the project
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('Starting build process...');

// Create public directory if it doesn't exist
const publicDir = path.join(__dirname, 'public');
if (!fs.existsSync(publicDir)) {
  fs.mkdirSync(publicDir);
}

// Copy all files to public directory
const copyDir = (src, dest) => {
  try {
    if (fs.existsSync(src)) {
      fs.mkdirSync(dest, { recursive: true });
      const entries = fs.readdirSync(src, { withFileTypes: true });

      for (const entry of entries) {
        const srcPath = path.join(src, entry.name);
        const destPath = path.join(dest, entry.name);

        if (entry.isDirectory()) {
          copyDir(srcPath, destPath);
        } else {
          fs.copyFileSync(srcPath, destPath);
        }
      }
    }
  } catch (err) {
    console.error(`Error copying ${src} to ${dest}:`, err);
  }
};

// Copy directories
['css', 'js', 'images', 'includes', 'php'].forEach(dir => {
  console.log(`Copying ${dir}...`);
  copyDir(path.join(__dirname, dir), path.join(publicDir, dir));
});

// Copy HTML files
console.log('Copying HTML files...');
fs.readdirSync(__dirname)
  .filter(file => file.endsWith('.html'))
  .forEach(file => {
    fs.copyFileSync(path.join(__dirname, file), path.join(publicDir, file));
  });

// Create a simple index.php if it doesn't exist
if (!fs.existsSync(path.join(publicDir, 'index.php'))) {
  console.log('Creating index.php...');
  fs.writeFileSync(
    path.join(publicDir, 'index.php'),
    '<?php header(\'Location: /index.html\'); ?>'
  );
}

// Fix file permissions
console.log('Setting file permissions...');
if (process.platform !== 'win32') {
  try {
    execSync('chmod -R 755 ' + publicDir);
  } catch (err) {
    console.error('Error setting file permissions:', err);
  }
}

console.log('Build completed successfully!');
