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

You need PHP >= 5 and a web server (we highly recommend nginx).

You can test the application with the built-in php server, but it should NOT be used in production:
```sh
~$ php -S http://localhost:8088
```


## Citation

```bib
@InProceedigs{swipe_dataset,
  author    = {Luis A. Leiva and Sunjun Kim Wenzhe Cui and Xiaojun Bi and Antti Oulasvirta},
  title     = {How We Swipe: A Large-scale Shape-writing Dataset and Empirical Findings},
  booktitle = {Proc. MobileHCI},
  year      = {2021},
}
```
