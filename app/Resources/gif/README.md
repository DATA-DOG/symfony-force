# How to create starwars gif

You will need: `recordmydesktop ffmpeg`

- run `recordmydesktop` in shell, keep it running..
- open **star.html** in your browser: `chromium star.html` make it fullscreen and refresh to run through.
- cancel `recordmydesktop` CTRL+C
- convert ogv to gif: `ffmpeg -ss 00:00:05.20 -i out.ogv -r 65 -s 640x480 starwars.gif`

**NOTE:** the `-ss` option sets the start time offset.

You may download latest ffmpeg from here:

    wget http://ffmpeg.gusari.org/static/64bit/ffmpeg.static.64bit.latest.tar.gz
    tar -xzf ffmpeg.static.64bit.latest.tar.gz
