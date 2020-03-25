(function() {
    function getKeyboards(self) {
        // Shorthand methods. See default settings below.
        var w = self.settings.keyWidth;
        var h = self.settings.keyHeight;
        var l = self.settings.keyHorizontalMargin || self.settings.keyMargin;
        var t = self.settings.keyVerticalMargin || self.settings.keyMargin;

        function x(col, offset) {
            return w * col + l + (offset || 0);
        }

        function y(row, offset) {
            return h * row + t + (offset || 0);
        }

        return [
            // Traditional QWERTY layout.
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

                    [x(0, w/2),   y(3), w*2, h*0.7, ''],
                    [x(1, w*1.5), y(3), w,   h*0.7, ''],
                    [x(2, w*1.5), y(3), w*4, h*0.7, ''],
                    [x(6, w*1.5), y(3), w,   h*0.7, ''],
                    [x(7, w*1.5), y(3), w,   h*0.7, ''],
                ],
            },
            // Numerical layout. Not used in the swipetest.
            {
                'name': '#1',
                'keys': [
                    [x(0), y(0), w, h, '1'],
                    [x(1), y(0), w, h, '2'],
                    [x(2), y(0), w, h, '3'],
                    [x(3), y(0), w, h, '4'],
                    [x(4), y(0), w, h, '5'],
                    [x(5), y(0), w, h, '6'],
                    [x(6), y(0), w, h, '7'],
                    [x(7), y(0), w, h, '8'],
                    [x(8), y(0), w, h, '9'],
                    [x(9), y(0), w, h, '0'],

                    [x(0, w/2), y(1), w, h, '!'],
                    [x(1, w/2), y(1), w, h, '"'],
                    [x(2, w/2), y(1), w, h, '·'],
                    [x(3, w/2), y(1), w, h, '$'],
                    [x(4, w/2), y(1), w, h, '%'],
                    [x(5, w/2), y(1), w, h, '&'],
                    [x(6, w/2), y(1), w, h, '/'],
                    [x(7, w/2), y(1), w, h, '('],
                    [x(8, w/2), y(1), w, h, ')'],

                    // [x(0, 3*w/2), y(2), w, h, "←"],
                    // [x(1, 3*w/2), y(2), w*5, h, " "],
                    // [x(6, 3*w/2), y(2), w, h, "→"],
                ],
            },
            // Numerical layout. Not used in the swipetest.
            {
                'name': '#2',
                'keys': [
                    [x(0), y(0), w, h, '\''],
                    [x(1), y(0), w, h, '"'],
                    [x(2), y(0), w, h, ':'],
                    [x(3), y(0), w, h, ';'],
                    [x(4), y(0), w, h, '-'],
                    [x(5), y(0), w, h, '+'],
                    [x(6), y(0), w, h, '*'],
                    [x(7), y(0), w, h, '?'],
                    [x(8), y(0), w, h, '^'],
                    [x(9), y(0), w, h, 'ç'],

                    [x(0, w/2), y(1), w, h, '`'],
                    [x(1, w/2), y(1), w, h, '~'],
                    [x(2, w/2), y(1), w, h, '<'],
                    [x(3, w/2), y(1), w, h, '>'],
                    [x(4, w/2), y(1), w, h, '{'],
                    [x(5, w/2), y(1), w, h, '}'],
                    [x(6, w/2), y(1), w, h, '['],
                    [x(7, w/2), y(1), w, h, ']'],
                    [x(8, w/2), y(1), w, h, '\\'],

                    // [x(0, 3*w/2), y(2), w, h, "←"],
                    // [x(1, 3*w/2), y(2), w*5, h, " "],
                    // [x(6, 3*w/2), y(2), w, h, "→"],
                ],
            },
        ];
    };

    // Expose module.
    var keyboard = {

        // Deault keyboard settings. Globally available.
        settings: {
            offsetTop: 0,
            offsetLeft: 0,
            keyWidth: 15,
            keyHeight: 15,
            keyMargin: 0,
            keyVerticalMargin: 0,
            keyHorizontalMargin: 0,
        },

        // getKeyboards: function() {
        //     return getKeyboards(this);
        // },

        getLayout: function(index) {
            var keyboards = getKeyboards(this);
            var keyb = [];
            var keys = keyboards[index || 0].keys;
            var hMargin = this.settings.keyHorizontalMargin || this.settings.keyMargin;
            var vMargin = this.settings.keyVerticalMargin || this.settings.keyMargin;
            for (var i = 0; i < keys.length; i++) {
                var row = keys[i];
                keyb.push({
                    x: row[0] + this.settings.offsetLeft,
                    y: row[1] + this.settings.offsetTop,
                    width: row[2] - hMargin,
                    height: row[3] - vMargin,
                    char: row[4],
                    dummy: !row[4],
                });
            }
            return keyb;
        },

        measure: function(index) {
            var keys = this.getLayout(index);
            var hSize = 0;
            var vSize = 0;
            var prevY = this.settings.offsetTop;
            var prevX = this.settings.offsetLeft;
            var rows = 0;
            var cols = 0;
            var hMargin = this.settings.keyHorizontalMargin || this.settings.keyMargin;
            var vMargin = this.settings.keyVerticalMargin || this.settings.keyMargin;
            for (var i = 0; i < keys.length; i++) {
                var k = keys[i];
                if (i === 0 || k.x > prevX) {
                    hSize += k.width + hMargin;
                    cols++;
                    prevX = k.x;
                }
                if (i === 0 || k.y > prevY) {
                    vSize += k.height + vMargin;
                    rows++;
                    prevY = k.y;
                }
            }
            return {width: hSize, height: vSize, numRows: rows, numCols: cols};
        },

    };

    // Expose module.
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = keyboard;
    } else if (typeof window !== 'undefined' && !window.virtualKeyboard) {
        // TODO: We should use a namespace to avoid clashing with others.
        window.virtualKeyboard = keyboard;
    }
})();
