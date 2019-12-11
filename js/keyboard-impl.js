(function() {

  function getKeyboards(self) {
    // Shorthand methods. See default settings below.
    var w = self.settings.keyWidth
      , h = self.settings.keyHeight
      , l = self.settings.keyHorizontalMargin || self.settings.keyMargin
      , t = self.settings.keyVerticalMargin || self.settings.keyMargin
      ;

    function x(row, col, offset) {
      return w * col + l + (offset || 0);
    }

    function y(row, col, offset) {
      return h * row + t + (offset || 0);
    }

    return [
      // Traditional QWERTY layout.
      { 'name': "abc",
        'keys': [
        [ x(0, 0),         y(0),  w,  h, "q" ],
        [ x(0, 1),         y(0),  w,  h, "w" ],
        [ x(0, 2),         y(0),  w,  h, "e" ],
        [ x(0, 3),         y(0),  w,  h, "r" ],
        [ x(0, 4),         y(0),  w,  h, "t" ],
        [ x(0, 5),         y(0),  w,  h, "y" ],
        [ x(0, 6),         y(0),  w,  h, "u" ],
        [ x(0, 7),         y(0),  w,  h, "i" ],
        [ x(0, 8),         y(0),  w,  h, "o" ],
        [ x(0, 9),         y(0),  w,  h, "p" ],

        [ x(1, 0, w/2),    y(1),  w,  h, "a" ],
        [ x(1, 1, w/2),    y(1),  w,  h, "s" ],
        [ x(1, 2, w/2),    y(1),  w,  h, "d" ],
        [ x(1, 3, w/2),    y(1),  w,  h, "f" ],
        [ x(1, 4, w/2),    y(1),  w,  h, "g" ],
        [ x(1, 5, w/2),    y(1),  w,  h, "h" ],
        [ x(1, 6, w/2),    y(1),  w,  h, "j" ],
        [ x(1, 7, w/2),    y(1),  w,  h, "k" ],
        [ x(1, 8, w/2),    y(1),  w,  h, "l" ],

        [ x(2, 0, w*1.5),  y(2),  w,  h, "z" ],
        [ x(2, 1, w*1.5),  y(2),  w,  h, "x" ],
        [ x(2, 2, w*1.5),  y(2),  w,  h, "c" ],
        [ x(2, 3, w*1.5),  y(2),  w,  h, "v" ],
        [ x(2, 4, w*1.5),  y(2),  w,  h, "b" ],
        [ x(2, 5, w*1.5),  y(2),  w,  h, "n" ],
        [ x(2, 6, w*1.5),  y(2),  w,  h, "m" ],

        [ x(3, 0, w/2),    y(3),  w*2,  h*0.7, "", true],
        [ x(3, 1, w*1.5),  y(3),  w,    h*0.7, "", true],
        [ x(3, 2, w*1.5),  y(3),  w*4,  h*0.7, "", true], // <-- true means "dummy" key
        [ x(3, 6, w*1.5),  y(3),  w,    h*0.7, "", true],
        [ x(3, 7, w*1.5),  y(3),  w,    h*0.7, "", true],

      ]},

      // Numerical layout.
      { 'name': "#1",
        'keys': [
        [ x(0, 0),  y(0),  w,  h, "1" ],
        [ x(0, 1),  y(0),  w,  h, "2" ],
        [ x(0, 2),  y(0),  w,  h, "3" ],
        [ x(0, 3),  y(0),  w,  h, "4" ],
        [ x(0, 4),  y(0),  w,  h, "5" ],
        [ x(0, 5),  y(0),  w,  h, "6" ],
        [ x(0, 6),  y(0),  w,  h, "7" ],
        [ x(0, 7),  y(0),  w,  h, "8" ],
        [ x(0, 8),  y(0),  w,  h, "9" ],
        [ x(0, 9),  y(0),  w,  h, "0" ],

        [ x(1, 0, w/2),  y(1),  w,  h, "!",  ],
        [ x(1, 1, w/2),  y(1),  w,  h, "\"" ],
        [ x(1, 2, w/2),  y(1),  w,  h, "·" ],
        [ x(1, 3, w/2),  y(1),  w,  h, "$" ],
        [ x(1, 4, w/2),  y(1),  w,  h, "%" ],
        [ x(1, 5, w/2),  y(1),  w,  h, "&" ],
        [ x(1, 6, w/2),  y(1),  w,  h, "/" ],
        [ x(1, 7, w/2),  y(1),  w,  h, "(" ],
        [ x(1, 8, w/2),  y(1),  w,  h, ")" ],

//        [ x(2, 0, 3*w/2), y(2), w, h, "←" ],
//        [ x(2, 1, 3*w/2), y(2), w*5, h, " " ],
//        [ x(2, 6, 3*w/2), y(2), w, h, "→" ],
      ]},

      // Numerical layout.
      { 'name': "#2",
        'keys': [
        [ x(0, 0),  y(0),  w,  h, "'" ],
        [ x(0, 1),  y(0),  w,  h, '"' ],
        [ x(0, 2),  y(0),  w,  h, ":" ],
        [ x(0, 3),  y(0),  w,  h, ";" ],
        [ x(0, 4),  y(0),  w,  h, "-" ],
        [ x(0, 5),  y(0),  w,  h, "+" ],
        [ x(0, 6),  y(0),  w,  h, "*" ],
        [ x(0, 7),  y(0),  w,  h, "?" ],
        [ x(0, 8),  y(0),  w,  h, "^" ],
        [ x(0, 9),  y(0),  w,  h, "ç" ],

        [ x(1, 0, w/2),  y(1),  w,  h, "`" ],
        [ x(1, 1, w/2),  y(1),  w,  h, "~" ],
        [ x(1, 2, w/2),  y(1),  w,  h, "<" ],
        [ x(1, 3, w/2),  y(1),  w,  h, ">" ],
        [ x(1, 4, w/2),  y(1),  w,  h, "{" ],
        [ x(1, 5, w/2),  y(1),  w,  h, "}" ],
        [ x(1, 6, w/2),  y(1),  w,  h, "[" ],
        [ x(1, 7, w/2),  y(1),  w,  h, "]" ],
        [ x(1, 8, w/2),  y(1),  w,  h, "\\" ],

//        [ x(2, 0, 3*w/2), y(2), w, h, "←" ],
//        [ x(2, 1, 3*w/2), y(2), w*5, h, " " ],
//        [ x(2, 6, 3*w/2), y(2), w, h, "→" ],
      ]}

    ];
  };

  // Expose module
  var keyboard = {

    // Deault keyboard settings. Globally available.
    settings: {
        offsetTop: 0
      , offsetLeft: 0
      , keyWidth: 15
      , keyHeight: 15
      , keyMargin: 0
      , keyVerticalMargin: 0
      , keyHorizontalMargin: 0
    },

//  getKeyboards: function() {
//      return getKeyboards(this);
//  },

    getLayout: function(index) {
      var keyboards = getKeyboards(this);
      var keyb = [];
      var keys = keyboards[index || 0].keys;
      var hMargin = this.settings.keyHorizontalMargin || this.settings.keyMargin,
          vMargin = this.settings.keyVerticalMargin || this.settings.keyMargin;
      for (var i = 0; i < keys.length; i++) {
        var row = keys[i];
        keyb.push({
            x:      row[0]   + this.settings.offsetLeft
          , y:      row[1] + this.settings.offsetTop
          , width:  row[2] - hMargin
          , height: row[3] - vMargin
          , char:   row[4]
          , dummy:  row[5]
        });
      }
      return keyb;
    },

    measure: function(index) {
      var keys = this.getLayout(index);
      var hSize = 0,
          vSize = 0;
      var prevY = this.settings.offsetTop,
          prevX = this.settings.offsetLeft;
      var rows = 0,
          cols = 0;
      var hMargin = this.settings.keyHorizontalMargin || this.settings.keyMargin,
          vMargin = this.settings.keyVerticalMargin || this.settings.keyMargin;
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
      return { width: hSize, height: vSize, numRows: rows, numCols: cols };
    }

  };

  // Expose module.
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = keyboard;
  } else if (typeof window !== 'undefined' && !window.virtualKeyboard) {
    // TODO: We should use a namespace to avoid clashing with others.
    window.virtualKeyboard = keyboard;
  }

})();
