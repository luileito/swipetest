# Shape-writing test

This is the source code of the typing test application of our MobileHCI'21 paper:

- L. A. Leiva, S. Kim, W. Cui, X. Bi, A. Oulasvirta.
  **How We Swipe: A Large-scale Shape-writing Dataset and Empirical Findings.**
  *Proc. MobileHCI, 2021.*

Our online test prompts each user with 16 short sentences.
The first sentence is a warm-up sentence which is not logged.
The user has to enter each sentence by swiping on a custom virtual keyboard that logs low-level information about gesture typing, 
such as the gesture path drawn on top of the keyboard or the time lapsed between consecutively swiped keys.


## Install

Windows OS is not supported.
A Unix-like OS is expected with GNU awk installed. In Debian-based Linux distributions you can run `sudo apt install gawk`.

You need PHP >= 5 and a web server (we highly recommend nginx).

You can test the application with the built-in php server, but it should NOT be used in production:
```sh
~$ php -S localhost:8088
```

## Translate

To translate the user interface, GNU gettext is required. In Debian-based Linux distributions you can run `sudo apt install gettext`.

You can run `translate.sh` to handle the whole translation process in one command.
Example: To translate the app to Spanish, run `bash translate.sh es_ES`, then edit the generated `locale/es_ES/messages.po` file with your Spanish translations and finally run `bash translate.sh es_ES` again to update the app. You must restart/reload your web server afterwards.

## Citation

```bib
@InProceedigs{swipe_dataset,
  author    = {Luis A. Leiva and Sunjun Kim Wenzhe Cui and Xiaojun Bi and Antti Oulasvirta},
  title     = {How We Swipe: A Large-scale Shape-writing Dataset and Empirical Findings},
  booktitle = {Proc. MobileHCI},
  year      = {2021},
}
```
