(function() {

  function getKeyboards(self) {
    // Shorthand methods.
    var w = self.settings.keyWidth
      , h = self.settings.keyHeight
      , l = self.settings.keyHorizontalMargin || self.settings.keyMargin
      , t = self.settings.keyVerticalMargin || self.settings.keyMargin
      ;

    function kx(row, col, offset) {
      return w * col + l + (offset || 0);
    }

    function ky(row, col, offset) {
      return h * row + t + (offset || 0);
    }

    return [

      { 'name': "abc"
      , 'keys': [
        kx(0, 0),  ky(0),  w,  h, "q",
        kx(0, 1),  ky(0),  w,  h, "w",
        kx(0, 2),  ky(0),  w,  h, "e",
        kx(0, 3),  ky(0),  w,  h, "r",
        kx(0, 4),  ky(0),  w,  h, "t",
        kx(0, 5),  ky(0),  w,  h, "y",
        kx(0, 6),  ky(0),  w,  h, "u",
        kx(0, 7),  ky(0),  w,  h, "i",
        kx(0, 8),  ky(0),  w,  h, "o",
        kx(0, 9),  ky(0),  w,  h, "p",

        kx(1, 0, w/2),  ky(1),  w,  h, "a",
        kx(1, 1, w/2),  ky(1),  w,  h, "s",
        kx(1, 2, w/2),  ky(1),  w,  h, "d",
        kx(1, 3, w/2),  ky(1),  w,  h, "f",
        kx(1, 4, w/2),  ky(1),  w,  h, "g",
        kx(1, 5, w/2),  ky(1),  w,  h, "h",
        kx(1, 6, w/2),  ky(1),  w,  h, "j",
        kx(1, 7, w/2),  ky(1),  w,  h, "k",
        kx(1, 8, w/2),  ky(1),  w,  h, "l",

//        kx(1, 0, w/2),    ky(2),  w,  h, "\u21E7",
        kx(2, 0, w*1.5),  ky(2),  w,  h, "z",
        kx(2, 1, w*1.5),  ky(2),  w,  h, "x",
        kx(2, 2, w*1.5),  ky(2),  w,  h, "c",
        kx(2, 3, w*1.5),  ky(2),  w,  h, "v",
        kx(2, 4, w*1.5),  ky(2),  w,  h, "b",
        kx(2, 5, w*1.5),  ky(2),  w,  h, "n",
        kx(2, 6, w*1.5),  ky(2),  w,  h, "m",
//        kx(2, 7, w*1.5),  ky(2),  w,  h, "\u21D0",

//        kx(3, 1, w*1.5), ky(3), w*5, h, " ",

//        kx(2, 7, w),  ky(2),  w,  h, ",",
//        kx(2, 8, w),  ky(2),  w,  h, ".",
//        kx(2, 7, w),  ky(2),  w+w/2,  h, "\u21D0",

//        kx(3, 0, w*2), ky(3), w, h, "←",
//        kx(3, 1, w*2), ky(3), w*5, h, " ",
//        kx(3, 6, w*2), ky(3), w, h, "→",
      ]}
    ,
      { 'name': "#1"
      , 'keys': [
        kx(0, 0),  ky(0),  w,  h, "1",
        kx(0, 1),  ky(0),  w,  h, "2",
        kx(0, 2),  ky(0),  w,  h, "3",
        kx(0, 3),  ky(0),  w,  h, "4",
        kx(0, 4),  ky(0),  w,  h, "5",
        kx(0, 5),  ky(0),  w,  h, "6",
        kx(0, 6),  ky(0),  w,  h, "7",
        kx(0, 7),  ky(0),  w,  h, "8",
        kx(0, 8),  ky(0),  w,  h, "9",
        kx(0, 9),  ky(0),  w,  h, "0",

        kx(1, 0, w/2),  ky(1),  w,  h, "!",
        kx(1, 1, w/2),  ky(1),  w,  h, "\"",
        kx(1, 2, w/2),  ky(1),  w,  h, "·",
        kx(1, 3, w/2),  ky(1),  w,  h, "$",
        kx(1, 4, w/2),  ky(1),  w,  h, "%",
        kx(1, 5, w/2),  ky(1),  w,  h, "&",
        kx(1, 6, w/2),  ky(1),  w,  h, "/",
        kx(1, 7, w/2),  ky(1),  w,  h, "(",
        kx(1, 8, w/2),  ky(1),  w,  h, ")",

//        kx(2, 0, 3*w/2), ky(2), w, h, "←",
        kx(2, 1, 3*w/2), ky(2), w*5, h, " ",
//        kx(2, 6, 3*w/2), ky(2), w, h, "→",
      ]}
    ,
      { 'name': "#2"
      , 'keys': [
        kx(0, 0),  ky(0),  w,  h, "'",
        kx(0, 1),  ky(0),  w,  h, '"',
        kx(0, 2),  ky(0),  w,  h, ":",
        kx(0, 3),  ky(0),  w,  h, ";",
        kx(0, 4),  ky(0),  w,  h, "-",
        kx(0, 5),  ky(0),  w,  h, "+",
        kx(0, 6),  ky(0),  w,  h, "*",
        kx(0, 7),  ky(0),  w,  h, "?",
        kx(0, 8),  ky(0),  w,  h, "^",
        kx(0, 9),  ky(0),  w,  h, "ç",

        kx(1, 0, w/2),  ky(1),  w,  h, "`",
        kx(1, 1, w/2),  ky(1),  w,  h, "~",
        kx(1, 2, w/2),  ky(1),  w,  h, "<",
        kx(1, 3, w/2),  ky(1),  w,  h, ">",
        kx(1, 4, w/2),  ky(1),  w,  h, "{",
        kx(1, 5, w/2),  ky(1),  w,  h, "}",
        kx(1, 6, w/2),  ky(1),  w,  h, "[",
        kx(1, 7, w/2),  ky(1),  w,  h, "]",
        kx(1, 8, w/2),  ky(1),  w,  h, "\\",

//        kx(2, 0, 3*w/2), ky(2), w, h, "←",
        kx(2, 1, 3*w/2), ky(2), w*5, h, " ",
//        kx(2, 6, 3*w/2), ky(2), w, h, "→",
      ]}

    ];
  };

  // Expose module
  var keyboard = {

    settings: {
        offsetTop: 0
      , offsetLeft: 0
      , keyWidth: 15
      , keyHeight: 15
      , keyMargin: 0
      , keyVerticalMargin: 0
      , keyHorizontalMargin: 0
    }

//  , getKeyboards: function() {
//      return getKeyboards(this);
//  }

  , getLayout: function(index) {
      var keyboards = getKeyboards(this);
      var keyb = [];
      var keys = keyboards[index].keys;
      var hMargin = this.settings.keyHorizontalMargin || this.settings.keyMargin,
          vMargin = this.settings.keyVerticalMargin || this.settings.keyMargin;
      for (var i = 0; i < keys.length; i += 5) {
        keyb.push({
            x:      keys[i]   + this.settings.offsetLeft
          , y:      keys[i+1] + this.settings.offsetTop
          , width:  keys[i+2] - hMargin
          , height: keys[i+3] - vMargin
          , char:   keys[i+4]
        });
      }
      return keyb;
    }

  , measure: function(index) {
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
