# syno-tools

Personnal scripts to use with Synology NAS.

## downloadEpisodes.php

Automatically download missing episodes from SickBeard using Torrents.
It will search on kickass.to for torrent files (Synology seams to have BTSearch API implemented, but not documented).

__Requires__

* SickBeard from SynoCommunity

__Use__

    ./downloadEpisodes.php

## downloadMissingSubs.php

Download missing subtitles for the series configured in Sickbeard with a language different of english ("en").

__Requires__

* Sickbeard from SynoCommunity
* Subliminal from SynoCommunity (see notes below)

__Use__

    ./downloadMissingSubs.php

## Configuration and use

Copy inc/config.json.sample to inc/config.json and edit it according your configuration and needs.

### Use on your Synology NAS

Copy or clone the files in your home on your Synology NAS. Configure it as documented above.
You can now run the scripts manually and/or add an automatic task in the task manager.

## Notes about subliminal

### guessit lib version problem

Subliminal requires guessit lib v0.7.0 to work and is not compatible (for now) with guessit v0.7.1. Sadly, the guessit version installed on Synology NAS is v0.7.1.
To make it work, edit the following files as below:

__/volume1/@appstore/env/lib/python2.7/site-packages/subliminal/videos.py, (line 72)__:

* Line 72, from:
        guess = guessit.guess_file_info(path, 'autodetect')
* to:
        guess = guessit.guess_file_info(path, 'filename')

__/volume1/@appstore/env/lib/python2.7/site-packages/subliminal/core.py__

* Line 158, from :
        guess = guessit.guess_file_info(subtitle.release, 'autodetect')
* to:
        guess = guessit.guess_file_info(subtitle.release, 'filename')

### Run as a different user

By default, subliminal is ran by *subliminal* user. You may need to run it with a different user.
Follow these steps to run with a different user (you will need to login as root with SSH on your NAS):

1. Stop Subliminal in DSM (*Package manager* -> *Subliminal* -> *Action* -> *Stop*)
2. Change subliminal owner:
        chown -R YOUR_USER:users /volume1/@appstore/subliminal
3. Edit /etc/passwd and change the end of the line for your user from */sbin/nologin* to */bin/sh*. It should be something like that:
        YOUR_USER:x:1001:100::/var/services/homes/YOUR_USER:/bin/sh
4. Finally, change the user in the start script. Edit */var/packages/subliminal/scripts/start-stop-status* and change the line *USER="subliminal"* to *USER="YOUR_USER"*
5. Restart Subliminal in DSM