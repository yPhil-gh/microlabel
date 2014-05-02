# microlabel

A CMS to manage albums

![Screenshot](https://raw.githubusercontent.com/xaccrocheur/microlabel/master/img/microlabel-album_page.png)

### Features
- Baseless : Just drop your correctly tagged music files in the MUSIC dir
    - and µlabel will build one [index page](http://opensimo.org/play/)
    - and [one page per album](http://opensimo.org/play/?a=Les_Intouchables,Touche)
- Easy link sharing : Copy / paste the link, and the file will [play on click](http://tinyurl.com/k4vkzcp)
- Nice URLs
- Displays musicians album-basis meta-info (See *Optional album config*)
    - Youtube videos
    - Twitter link
    - Mail link
    - Gravatar
- Displays *any* image found in the album's dir as a gallery
- Full **Valid HTML** / JS / CSS (No flash)
    - Multilingual interface
    - Version checking
    - Secure: Your music dir location is concealed
- Back Office to manage / tag audio files

### Installation
- Drop it in a directory
- Point a web browser to this directory

### Configuration
- Locate the MUSIC directory
- Drop your (correctly tagged) audio files in it
- Edit *BO/.htpasswd* and [change your login / password](https://httpd.apache.org/docs/current/programs/htpasswd.html) (default is admin / demo)
- That's it

#### Optional config
- *chown .www-data CACHE && chmod g+w CACHE* to activate the caching system (faaaaster)
- Rename the microlabel dir with the name of your label (and keep on *git pull* from there)
- Replace *img/label/label_logo_** with your record company's ;) logo
- Edit the TEXT/* files to replace the text(s) with your own blurb
- Rename the MUSIC directory, and edit *libs/microlabel.php* accordingly
- Install [vorbiscomment](https://wiki.xiph.org/VorbisComment) to be able to **write** tags in the back-office
    - sudo apt-get install [vorbis-tools](https://wiki.xiph.org/Vorbis-tools)

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
    <email>name@domain.ext</email>
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
