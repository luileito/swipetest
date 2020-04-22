#!/usr/bin/env python3

'''
Analyze swipe dataset from ndjson file with computed stats.

---
Author: Luis A. Leiva <luis.leiva@aalto.fi>
Date created: 22/4/2020
Date last modified: 22/4/2020
Python Version: 3.5
'''

import sys
import os
import json

if __name__ == '__main__':

    ndjson_file = sys.argv[1]
    with open(ndjson_file) as f:
        for line in f:
            obj = json.loads(line)
            print(obj)
            exit()
