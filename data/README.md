# Shape-writing data

Our online test has 2 different phrase sets.
On the one hand, we use the 200 memorable sentences from the Enron mobile dataset [1].
On the other hand, we use 4-words sentences drawn from 4 different phrase sets we created according to the 10,000 most common English words (google-10000-english.txt) according to Google's Trillion Word Corpus [2] and according to Forbes' 2019 Global 2000 list [3]. These phrase sets are:
- highly frequent words, from the top 2k common words from [2].
- common words, from the next best 3k common words from [2].
- infrequent words, from the remaining 5k common words list from [2].
- out-of-vocabulary words, from [3] and also words from [2] that are not found in the English dictionary (we used the /usr/share/dict/words dictionary, which is available in all Unix systems).

One third of the sentences presented to every user are drawn from the Enron mobile dataset.
The remaining are 4-words sentences, composed on the fly by randomly picking one word from each of the above-mentioned 4 phrase sets.

In any case, all words are lowercased and words with any punctuation symbol are removed from our phrase sets.
We also remove 1-character words from the 4 word lists (e.g. "x", "e"), since we need at least 2 characters for swiping on a keyboard.
We did not remove swear words, in order to ensure ecological validity of our data.

**References**

1. https://www.keithv.com/software/enronmobile/
2. https://github.com/first20hours/google-10000-english
3. https://www.forbes.com/global2000/
