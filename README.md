# microlabel

A CMS to manage albums

### Features
- Baseless : Just drop your correctly tagged music files in the MUSIC dir
    - and Microlabel will build one index page
    - and one page per album
- Easy link sharing : Copy / paste the link, and the file will [play on click](http://tinyurl.com/k4vkzcp)
- Nice URLs
- Displays musicians album-basis meta-info (See *Optional album config*)
    - Youtube videos
    - Twitter link
    - Mail link
- Displays *any* image found in the album's dir as a gallery
- Full **Valid HTML** / JS / CSS (No flash)
    - Multilingual interface
    - Version checking
    - Secure: Your music dir location is concealed

### Installation
- Drop it in a directory
- Point a web browser to this directory

### Configuration
- Locate the MUSIC directory
- Drop your (correctly tagged) audio files in it
- That's it

#### Optional config
- Rename the microlabel dir with the name of your label
- Edit the TEXT/* files to replace the text with your own
- Rename the MUSIC directory, and edit the focllowing files accordingly
    - index.php
    - dl.php
    - BO/tagger.php
- Edit BO/.htpasswd and change your login / password (default is admin / demo)

#### Optional album config
- Drop an image whose filename begins with **bg-** to make a background for this album's page
- Put a **info.xml** with the following syntax in each album's directory

```
<?xml version="1.0" encoding="ISO-8859-1"?>
<microlabel>
  <musicien name="Name">
    <instrument>voice</instrument>
    <instrument>guitar</instrument>
    <instrument>bass</instrument>
    <twitter>twittername</twitter>
  </musicien>
  <musicien name="Other Name">
    <instrument>voice</instrument>
    <twitter>othertwittername</twitter>
  </musicien>

 <video>
    <name>Video name</name>
    <youtubeid>AD7L5T1lpbM</youtubeid>
  </video>
  <video>
    <name>Coffe and TV (Blur)</name>
    <youtubeid>cG-GbjYipio</youtubeid>
  </video>
</microlabel>
```
