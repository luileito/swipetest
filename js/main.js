$(function(){

  if (!('ontouchstart' in window)) {
      // Allow only mobile devices to enter the test.
      location.replace('error.php?e=no-touch');
  }

  if (navigator.platform.match(/iPhone|iPod|iPad/)) {
      // There are several issues with swipe gestures in iOS,
      // see e.g. https://github.com/nolimits4web/Swiper/issues/1140
      if (navigator.userAgent.match('CriOS')) {
          // There is no way to disable swipe gestures on Chrome iOS in any case,
          // so this browser is unsupported.
          location.replace('error.php?e=ios-chrome');
      } else if (history.length > 1) {
          // There is no way to disable swipe gestures on iOS when there is a previous page in the history,
          // so in this case users can copy the study link and access from a new tab.
          location.replace('error.php?e=ios');
      }
  }

  // At this point, we can proceed with the app.
  var keyboard = window.virtualKeyboard
    , currPos = { x:0, y:0 }
    , prevPos = { x:0, y:0 }
    , evQueue = []
    , $container = $('.container')
    , $sentence = $('.sentence')
    , $message = $('.message')
    , $keyboard = $('.keyboard')
    , sentenceHash = $sentence.data('hash')
    , keyboardIndex = 0
    , shapewritingKeys = []
    , renderedKeyboard = null
    , wordWarning = false
    , containerSize = { width: 0, height: 0 }
    ;

  // Configure the look and feel of keys.
  // TODO: Make key size proportional to available width.
  keyboard.settings.keyWidth = 30;
  keyboard.settings.keyHeight = 20;
  keyboard.settings.keyFontSize = 9;
  keyboard.settings.keyMargin = 1;
  keyboard.settings.keyCasing = 'uppercase';
  // Configure the look and feel of keys.
  keyboard.settings.keyCornerRadius = 0;
  keyboard.settings.keyStrokeColor = '#ddd';
  keyboard.settings.keyFillColor = '#bbb';
  keyboard.settings.keyTextColor = '#000';
  // Configure other rendering options.
  keyboard.settings.cursorSymbol = '❚';
  keyboard.settings.backgroundColor = '#fff';
  keyboard.settings.shapewritingColor = 'cyan';
  keyboard.settings.shapewritingSize = 6;
  keyboard.settings.globalCompositeOperation = 'multiply';

  // Setup text display.
  $message.html(keyboard.settings.cursorSymbol).on('click', function(ev) {
      $(this).html(keyboard.settings.cursorSymbol);
  });

  // Init sentence token.
  $sentence.find('span').first().addClass('current');

  function updateMeasures() {
      var layoutSize = keyboard.measure(keyboardIndex);

      var docWidth = $container.width();
      var docHeight = $container.height();
      if (docWidth > docHeight) {
          // Landscape mode.
          containerSize = {
              width: docWidth,
              height: docHeight * 0.6,
          };
          keyboard.settings.keyHeight = containerSize.height / layoutSize.numRows;
          keyboard.settings.keyWidth = containerSize.width / layoutSize.numCols;
      } else {
          // Portrait mode.
          containerSize = {
              width: docWidth,
              height: docHeight * 0.3,
          };
          keyboard.settings.keyWidth = containerSize.width / layoutSize.numCols;
          keyboard.settings.keyHeight = containerSize.height / layoutSize.numRows;
      }
      // Make font size proportional to available space as well.
      // For uppercase letters, `keyWidth / 3` looks good.
      keyboard.settings.keyFontSize = Math.min(15, Math.round(keyboard.settings.keyWidth / 3));
  }

  function logEvent(evName, touch) {
      var time = (new Date).getTime()
        , currTok = $sentence.find('.current').text()
        , keyboard_width = $keyboard.width()
        , keyboard_height = $keyboard.height()
        ;

      evQueue.push([
          sentenceHash,
          time,
          keyboard_width,
          keyboard_height,
          evName,
          touch.x,
          touch.y,
          touch.rx,
          touch.ry,
          touch.angle,
          currTok
      ]);
  }

  function touchPosition(event) {
      var k = $keyboard.get(0);
      var e = event.originalEvent;
      if (e.changedTouches) e = e.changedTouches[0];
      return {
          x: Math.round(e.clientX - k.offsetLeft)
        , y: Math.round(e.clientY - k.offsetTop)
        , rx: e.radiusX
        , ry: e.radiusY
        , angle: e.rotationAngle
      };
  }

  CanvasRenderingContext2D.prototype.roundRect = function(x, y, width, height, radius) {
      this.beginPath();
      this.moveTo(x + radius, y);
      this.lineTo(x + width - radius, y);
      this.quadraticCurveTo(x + width, y, x + width, y + radius);
      this.lineTo(x + width, y + height - radius);
      this.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
      this.lineTo(x + radius, y + height);
      this.quadraticCurveTo(x, y + height, x, y + height - radius);
      this.lineTo(x, y + radius);
      this.quadraticCurveTo(x, y, x + radius, y);
      this.closePath();
  }

  function drawKey(k) {
      var fs = keyboard.settings.keyFontSize
        , sc = keyboard.settings.keyStrokeColor
        , fc = keyboard.settings.keyFillColor
        , tc = keyboard.settings.keyTextColor
        ;

      var ctx = $keyboard.get(0).getContext('2d');
      ctx.fillStyle = fc;
      ctx.strokeStyle = sc;

      // Draw key box
      ctx.beginPath();
      if (keyboard.settings.keyCornerRadius) {
          ctx.roundRect(k.x,k.y, k.width,k.height, keyboard.settings.keyCornerRadius);
      } else {
          ctx.fillRect(k.x,k.y, k.width,k.height);
      }
      ctx.fill();
      ctx.stroke();

      var center = getKeyCenter(k);
      var radius = 0;
//      ctx.beginPath();
//      // DEBUG: Draw key centers.
//      ctx.fillStyle = 'red';
//      radius = keyboard.settings.keyFontSize;
//      ctx.arc(center.x, center.y, radius, 0, 2 * Math.PI);
//      ctx.fill();

//      // DEBUG: Draw key hitTarget areas.
//      ctx.beginPath();
//      ctx.strokeStyle = 'teal';
//      radius = 2.5 * Math.max(keyboard.settings.keyWidth, keyboard.settings.keyHeight) / 2;
//      ctx.arc(center.x, center.y, radius, 0, 2 * Math.PI);
//      ctx.stroke();

      // Draw key text.
      ctx.font = 'bold ' + fs + 'pt sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillStyle = tc;

      var keyVal = k.char;
      if (keyboard.settings.keyCasing.startsWith('lower')) {
          keyVal = keyVal.toLowerCase();
      } else if (keyboard.settings.keyCasing.startsWith('upper')) {
          keyVal = keyVal.toUpperCase();
      }
      // TODO: Allow label offsets.
      ctx.fillText( keyVal, (k.x + k.width/2), (k.y + k.height/2) );
  }

  function renderKeyboard() {
      var layoutSize = keyboard.measure(keyboardIndex);
      // Center keyboard on screen.
      keyboard.settings.offsetLeft = (containerSize.width - layoutSize.width)/2;
      keyboard.settings.offsetTop = (containerSize.height - layoutSize.height)/2;

      var keyboardCanvas = $keyboard.get(0);
      // Fill in all available space.
      keyboardCanvas.width = containerSize.width;
      keyboardCanvas.height = containerSize.height;

      var ctx = keyboardCanvas.getContext('2d');

//      // TODO: Implement the scaling trick to improve canvas resolution.
//      $keyboard.css({ 'width': containerSize.width/2, 'height': containerSize.height/2 });
//      ctx.scale(2, 2);

      // Draw background color.
      ctx.fillStyle = keyboard.settings.backgroundColor;
      ctx.fillRect(0, 0, keyboardCanvas.width, keyboardCanvas.height);
      // Draw keys.
      keyboard.getLayout(keyboardIndex).forEach(drawKey);

//      // DEBUG: Draw keyboard bounding box, to ensure we got the layout measures right.
//      ctx.strokeStyle = 'green';
//      ctx.rect(keyboard.settings.offsetLeft, keyboard.settings.offsetTop, layoutSize.width, layoutSize.height);
//      ctx.stroke();

      // Save a snapshot of the canvas.
      renderedKeyboard = ctx.getImageData(0, 0, keyboardCanvas.width, keyboardCanvas.height);
  }


  function onPointerPress(e) {
      e.preventDefault();

      var p = touchPosition(e);
      currPos = prevPos = p;

      var currentKey = getClosestKey(p);
      shapewritingKeys.push(currentKey);

      logEvent('touchstart', p);
  }

  function onPointerMove(e) {
      e.preventDefault();

      var p = touchPosition(e);
      currPos = p;

      var currentKey = getClosestKey(p);
      shapewritingKeys.push(currentKey);

      logEvent('touchmove', p);

      drawPath();
  }

  function onPointerRelease(e) {
      e.preventDefault();

      var p = touchPosition(e);
      currPos = prevPos = p;

      var currentKey = getClosestKey(p);
      shapewritingKeys.push(currentKey);

      logEvent('touchend', p);

      handleTextSwipe();
  }

  function handleTextSwipe() {
      // Get a list of unique consecutively entered chars;
      // e.g. `['a', 'a', 'b', 'a']` becomes `['a', 'b', 'a']`.
      var travelKeys = shapewritingKeys.filter(function(item, pos, arr) {
        return pos === 0 || item.char !== arr[pos-1].char;
      });

//      // DEBUG: For the records, statistical decoders have to deal with this gibberish text:
//      travelKeys.map(k => k.char).forEach(type);

      var ctx = $keyboard.get(0).getContext('2d');

      // Re-render layout to remove shapewriting path.
      ctx.putImageData(renderedKeyboard, 0, 0);

      // Verify that swipe path begins and ends in the right key.
      var $curWord = $sentence.find('.current');
      var todoText = $curWord.text();
      var iniKey = getKeyFromChar(todoText[0]);
      var iniTouch = shapewritingKeys[0];
      var endKey = getKeyFromChar(todoText[todoText.length - 1]);
      var endTouch = shapewritingKeys[shapewritingKeys.length - 1];
      var iniCenter = getKeyCenter(iniKey);
      var endCenter = getKeyCenter(endKey);
      var iniDist = distance(iniTouch, iniCenter);
      var endDist = distance(endTouch, endCenter);
      var maxDist = 2.5 * Math.max(keyboard.settings.keyWidth, keyboard.settings.keyHeight) / 2;
      var isWrong = iniDist > maxDist || endDist > maxDist;

      // Edge case: some words begin and start with the same char (e.g. "mum", "rooster")
      // therefore we have to ensure that a swipe gesture was actually used.
      var todoKeys = todoText.split('').filter(function(item, pos, arr) {
        return pos === 0 || item.char !== arr[pos-1].char;
      });
      if (travelKeys.length < todoKeys.length) isWrong = true;

      // Flag the whole swipe sequence as right or wrong.
      evQueue = evQueue.map(function(entry) {
          entry.push(+isWrong);
          return entry;
      });

//      // DEBUG: Draw sokgraph (expected swipe path).
//      var swipeKeys = todoText.split('').map(getKeyFromChar);
//      ctx.beginPath();
//      ctx.lineWidth = 2;
//      ctx.strokeStyle = 'red'
//      for (var i = 1; i < swipeKeys.length; i++) {
//          var prevKey = getKeyCenter(swipeKeys[i - 1]);
//          var currKey = getKeyCenter(swipeKeys[i]);
//          ctx.moveTo(prevKey.x, prevKey.y);
//          ctx.lineTo(currKey.x, currKey.y);
//      }
//      ctx.stroke();

      if (isWrong) {
          // Probably swiped wrongly.
          $curWord.addClass('wrong');

          sendEvents(todoText);

//          // Alert user sparingly.
//          if (!wordWarning) {
//              alert('Please try again');
//              wordWarning = true;
//          }
      } else {
          // Update words state.
          // Don't remove the "todo" class because we'll use it to decide when the sentence is done.
          $curWord.removeClass('wrong current').addClass('done');
          $curWord.next().addClass('current');

          type(todoText);
          // Add ending space in shapewriting, as it's currently done in Gboard and Swype.
          type(' ');

          var todoWords = $sentence.find('.todo');
          var doneWords = $sentence.find('.done');
          var isDone = doneWords.length === todoWords.length;

          sendEvents(todoText, isDone);
      }

      shapewritingKeys = [];
  }

  function sendEvents(word, isDone) {
      $.ajax('save.php', {
          type: 'POST',
          data: {
              events: JSON.stringify(evQueue),
              isDone: isDone,
              word: word,
              sentence: sentenceHash,
          },
          complete: function() {
              evQueue = [];
              if (isDone) location.reload();
          },
      });
  }

  function drawPath(ctx) {
      if (!ctx) ctx = $keyboard.get(0).getContext('2d');

      ctx.globalCompositeOperation = keyboard.settings.globalCompositeOperation;

      ctx.beginPath();
      ctx.lineWidth = keyboard.settings.shapewritingSize;
      ctx.strokeStyle = keyboard.settings.shapewritingColor;
      ctx.lineCap = 'round';
      ctx.moveTo(prevPos.x, prevPos.y);
      ctx.lineTo(currPos.x, currPos.y);
      ctx.stroke();

      prevPos = currPos;
  }

//  function update() {
//    var keyboardCanvas = $keyboard.get(0);
//    var ctx = keyboardCanvas.getContext('2d');
//    ctx.globalCompositeOperation = keyboard.settings.globalCompositeOperation;

////    ctx.clearRect(0, 0, keyboardCanvas.width, keyboardCanvas.height);
////    ctx.putImageData(renderedKeyboard, 0, 0);

//    drawPath(ctx);

//    // Fade out the previous tails
//    ctx.fillStyle = `rgba(0, 0, 0, 0.1)`;
//    ctx.fillRect(0, 0, keyboardCanvas.width, keyboardCanvas.height);

//    requestAnimationFrame(update);
//  }

  function type(char) {
      switch (char) {
        case 'ENTER':
          actionEnter();
          break;
        case 'DELETE':
          actionDelete();
          break;
        default:
          actionType(char);
          break;
      }
  }

  function actionEnter() {
      // Not implemented.
  }

  function actionDelete() {
      // Not implemented.
  }

  function actionType(x) {
      x = x.toLowerCase();

      var cursor = keyboard.settings.cursorSymbol;
      var currentText = $message.html().trimLeft().replace(cursor, '');
      $message.html(currentText + x + cursor);
//      // TODO: Auto-scroll message content.
//      $message.scrollTop($message.height());
  }

  function getKeyCenter(k) {
      return {
          x: (k.x + k.width/2)
        , y: (k.y + k.height/2)
      };
  }

  function getClosestKey(p) {
      var keys = keyboard.getLayout(keyboardIndex)
        , dist = Number.MAX_VALUE
        , closest = null
        ;
      for (var i = 0; i < keys.length; i++) {
          // Save a copy of the original key object
          var k = $.extend({}, keys[i]);
          // Collision detection: touch point should be within some key bounds.
          if (p.x > k.x && p.x < k.x + k.width && p.y > k.y && p.y < k.y + k.height) {
              closest = k;
              break;
          } else {
              // Touch point outside any key bounds; might happen when clicking between keys.
              var center = getKeyCenter(k);
              var d = sqDistance(p, center);
              if (d < dist) {
                  dist = d;
                  closest = k;
              }
          }
      }
      return closest;
  }

  function getKeyFromChar(c) {
    var keys = keyboard.getLayout(keyboardIndex);
    for (var i = 0; i < keys.length; i++) {
        var k = keys[i];
        if (k.char.toLowerCase() === c.toLowerCase()) {
            return k;
        }
    }
    return false;
  }

  function sqDistance(a, b) {
      return Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2);
  }

  function distance(a, b) {
      return Math.sqrt(sqDistance(a, b));
  }

  // Init ----------------------------------------------------------------------

  // Prevent autohiding the address bar and disallow scrolling on the page.
  window.addEventListener('load', function(e) { window.scrollTo(0, 0); });
  document.body.addEventListener('touchmove', function(e) { e.preventDefault(); }, { passive: false });


  $keyboard
    .on('mousedown touchstart', onPointerPress)
    .on('mousemove touchmove', onPointerMove)
    .on('mouseup touchend', onPointerRelease)

  updateMeasures();
  renderKeyboard();

  // Listen to device orientation changes.
  // Notice that the layout size takes some time to update dimensions,
  // so we keep track of the BODY size until noticing the update.
  var currDocWidth = $container.width();
  window.addEventListener('orientationchange', function() {
      var sizeTimer = setInterval(function() {
          var docWidth = $container.width();
          if (docWidth !== currDocWidth) {
              clearInterval(sizeTimer);
              currDocWidth = docWidth;

              updateMeasures();
              renderKeyboard();
          }
      }, 100);
  });

});
