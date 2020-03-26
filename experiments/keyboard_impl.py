class Keyboard:

    def __init__(self, options={}):
        defaults = {
            'offsetTop': 0,
            'offsetLeft': 0,
            'keyWidth': 15,
            'keyHeight': 15,
            'keyMargin': 0,
            'keyVerticalMargin': 0,
            'keyHorizontalMargin': 0,
        }
        defaults.update(options)

        self.settings = defaults


    def getKeyboards(self):
        '''
        Provide a list of available keyboards.
        '''
        w = self.settings['keyWidth']
        h = self.settings['keyHeight']
        l = self.settings['keyHorizontalMargin'] or self.settings['keyMargin']
        t = self.settings['keyVerticalMargin'] or self.settings['keyMargin']

        def x(col, offset=0):
            return w * col + l + offset

        def y(row, offset=0):
            return h * row + t + offset

        return [
            # Traditional QWERTY layout.
            {
                'name': 'abc',
                'keys': [
                    [x(0), y(0), w, h, 'q'],
                    [x(1), y(0), w, h, 'w'],
                    [x(2), y(0), w, h, 'e'],
                    [x(3), y(0), w, h, 'r'],
                    [x(4), y(0), w, h, 't'],
                    [x(5), y(0), w, h, 'y'],
                    [x(6), y(0), w, h, 'u'],
                    [x(7), y(0), w, h, 'i'],
                    [x(8), y(0), w, h, 'o'],
                    [x(9), y(0), w, h, 'p'],

                    [x(0, w/2), y(1), w, h, 'a'],
                    [x(1, w/2), y(1), w, h, 's'],
                    [x(2, w/2), y(1), w, h, 'd'],
                    [x(3, w/2), y(1), w, h, 'f'],
                    [x(4, w/2), y(1), w, h, 'g'],
                    [x(5, w/2), y(1), w, h, 'h'],
                    [x(6, w/2), y(1), w, h, 'j'],
                    [x(7, w/2), y(1), w, h, 'k'],
                    [x(8, w/2), y(1), w, h, 'l'],

                    [x(0, w*1.5), y(2), w, h, 'z'],
                    [x(1, w*1.5), y(2), w, h, 'x'],
                    [x(2, w*1.5), y(2), w, h, 'c'],
                    [x(3, w*1.5), y(2), w, h, 'v'],
                    [x(4, w*1.5), y(2), w, h, 'b'],
                    [x(5, w*1.5), y(2), w, h, 'n'],
                    [x(6, w*1.5), y(2), w, h, 'm'],

                    # The last row shold have only the space key.
                    # However, in shape-writing we don't use spaces,
                    # so let's a a bunch of "dummy" keys to mimic a conventional QWERTY layout.
                    [x(0, w/2),   y(3), w*2, h*0.7, ''],
                    [x(1, w*1.5), y(3), w,   h*0.7, ''],
                    [x(2, w*1.5), y(3), w*4, h*0.7, ''],
                    [x(6, w*1.5), y(3), w,   h*0.7, ''],
                    [x(7, w*1.5), y(3), w,   h*0.7, ''],
                ],
            },
        ]


    def getLayout(self, index=0):
        '''
        Get layout at given index within the list of available keyboards.
        '''
        keyboards = self.getKeyboards()
        keyb = []
        keys = keyboards[index]['keys']
        hMargin = self.settings['keyHorizontalMargin'] or self.settings['keyMargin']
        vMargin = self.settings['keyVerticalMargin'] or self.settings['keyMargin']
        for k in keys:
            keyb.append({
                'x': k[0] + self.settings['offsetLeft'],
                'y': k[1] + self.settings['offsetTop'],
                'width': k[2] - hMargin,
                'height': k[3] - vMargin,
                'char': k[4],
            })

        return keyb


    def measure(self, index=0):
        '''
        Measure keyboard layout.
        '''
        keyb = self.getLayout(index)
        hSize = 0
        vSize = 0
        prevY = self.settings['offsetTop']
        prevX = self.settings['offsetLeft']
        rows = 0
        cols = 0
        hMargin = self.settings['keyHorizontalMargin'] or self.settings['keyMargin']
        vMargin = self.settings['keyVerticalMargin'] or self.settings['keyMargin']
        for i, k in enumerate(keyb):
            if (i == 0 or k['x'] > prevX):
                hSize += k['width'] + hMargin
                cols += 1
                prevX = k['x']
            if (i == 0 or k['y'] > prevY):
                vSize += k['height'] + vMargin
                rows += 1
                prevY = k['y']

        return {
            'width': hSize,
            'height': vSize,
            'numRows': rows,
            'numCols': cols,
        }
