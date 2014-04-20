microlabel
==========

A CMS to manage albums

Installation
-----
- Drop it in a directory
- Point a web browser to this directory


Configuration
-----
- Locate the MUSIC directory
- Drop your (correctly tagged) audio files in it
- That's it

Ooptional config
------

    - Drop an image whose filename begins with `bg-` to make a background for this album's page
    - Put a info.xml with the following syntax :

```
 <?xml version="1.0" encoding="ISO-8859-1"?>
 <simo>
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
```
