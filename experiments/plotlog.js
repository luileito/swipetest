#!/usr/bin/env node

//
// This program renders a swipe trajectory over the QWERTY layout as specified in the log file.
//
// Usage:
// - Plot well-swiped word: node plotlog.js -l ../logs/file.log -w hello
// - Plot fail-swiped word: node plotlog.js -l ../logs/file.log -w hello -f
//

var fs        = require("fs")
  , path      = require("path")
  , getopt    = require("node-getopt")
  , parser    = require("./logparser")
  , keyboard  = require("./keyboard-impl")
  , Canvas    = require("canvas")
  , cliConf   = [
    ['l' , 'logFile=ARG'  , 'Log file'],
    ['w' , 'word=ARG'     , 'Word to plot'],
    ['f' , 'failed'       , 'Show failed word, if present'],
    ['v' , 'verbose'      , 'Display debug info'],
    ['h' , 'help'         , 'Display this help']
  ]
  , cli       = getopt.create(cliConf).bindHelp()
  , opt       = cli.parseSystem()
  , args      = opt.options
  ;

// Font definitions MUST happen before the canvas is created.
Canvas.registerFont('arialbd.ttf', { family: 'Arial', weight: 'bold' });

// FIXME: Use globals sparingly.
var keyboardIndex = 0
  , containerSize
  , csvContent
  // outFile will be something like /tmp/u69o0276ivts8vvbj8ffuj6oc3-hello-0.png
  , outFile = '/tmp/' + path.basename(args.logFile, '.log') + '-' + args.word + '-' + (+args.failed || 0) + '.png'
  ;

console.log('Writing to %s', outFile)

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
keyboard.settings.backgroundColor = '#fff';
keyboard.settings.shapewritingColor = 'cyan';
keyboard.settings.shapewritingSize = 6;

parser.load(args.logFile).then(main).catch(console.error);

function main(data) {
    // Expose data globally.
    csvContent = data;

    // Since we want to draw only one word, ignore the rest.
    csvContent = csvContent.filter(row => row.word === args.word && row.isFailedWord === !!args.failed);

    if (!csvContent.length) {
        console.error('Word "%s" (failed: %s) not found in %s', args.word, !!args.failed, args.logFile);
        throw new Error('Please revise your CLI arguments.');
    }

    var numWords = countWords();
    if (numWords > 1) {
        console.warn('Multiple swiping attempts (%d) of word "%s" detected. Will plot the last attempt only.', numWords, args.word);
    }

    plot();
}

function countWords() {
    // There should be exactly one touchend/touchstart event per word,
    // unless there were duplicated words in the sentence (TODO: check this).
    var rows = csvContent.filter(row => {
        // Ignore failed attempts unless explicitly stated.
        var check = args.failed ? row.isFailedWord : !row.isFailedWord;
        return row.event === 'touchend' && check;
    });

    return rows.length;
}

function plot() {
    var row = csvContent[0];
    var cnv = Canvas.createCanvas(row.canvasWidth, row.canvasHeight, 'hey');
    var ctx = cnv.getContext('2d');

    ctx.roundRect = function(x, y, width, height, radius) {
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

    updateMeasures();
    renderKeyboard(cnv);

//    // DEBUG: Draw sokgraph (expected swipe path).
//    var swipeKeys = row.word.split('').map(getKeyFromChar);
//    ctx.beginPath();
//    ctx.lineWidth = 2;
//    ctx.strokeStyle = 'red'
//    for (var i = 1; i < swipeKeys.length; i++) {
//        var prevKey = getKeyCenter(swipeKeys[i - 1]);
//        var currKey = getKeyCenter(swipeKeys[i]);
//        ctx.moveTo(prevKey.x, prevKey.y);
//        ctx.lineTo(currKey.x, currKey.y);
//    }
//    ctx.stroke();

    ctx.beginPath();
    ctx.lineWidth   = keyboard.settings.shapewritingSize;
    ctx.strokeStyle = keyboard.settings.shapewritingColor;
    ctx.lineCap = 'round';

    // TODO: Decide the best overlay color style.
    // ctx.globalAlpha = 0.5;
    ctx.globalCompositeOperation = 'multiply';

    var trajectory = getPoints();
    for (var i = 0; i < trajectory.length - 1; i++) {
        var currPt = trajectory[i];
        var nextPt = trajectory[i+1];
        ctx.moveTo(currPt.x, currPt.y);
        ctx.lineTo(nextPt.x, nextPt.y);
    }

    ctx.stroke();
    ctx.closePath();

    var stream = cnv.createPNGStream();
    saveRasterFile(stream, outFile);
}

function saveRasterFile(stream, outFile) {
    var out = fs.createWriteStream(outFile);
    stream.on('end', console.log);
    stream.on('error', console.error);
    stream.pipe(out);
}

function getPoints() {
    var seenEv = {};
    var points = [];
    for (var i = 0; i < csvContent.length; i++) {
        var row = csvContent[i];

        if (row.event === 'touchstart' && seenEv[row.event]) {
            break;
        }

        seenEv[row.event] = true;

        if (row.event === 'touchmove') {
            points.push({x:row.x, y:row.y});
        }
    }
    return points;
}

function updateMeasures() {
    var layoutSize = keyboard.measure(keyboardIndex);

    var row = csvContent[0];
    var docWidth = row.canvasWidth;
    var docHeight = row.canvasHeight;

    containerSize = {
        width: docWidth,
        height: docHeight,
    };

    if (docWidth > docHeight) {
        keyboard.settings.keyHeight = containerSize.height / layoutSize.numRows;
        keyboard.settings.keyWidth = containerSize.width / layoutSize.numCols;
    } else {
        keyboard.settings.keyWidth = containerSize.width / layoutSize.numCols;
        keyboard.settings.keyHeight = containerSize.height / layoutSize.numRows;
    }
    // Make font size proportional to available space as well.
    // For uppercase letters, `keyWidth / 3` looks good.
    // NB: Notice that the Canvas module is different from the canvas element in browsers,
    // so font definitions and sizes can vary slightly.
    keyboard.settings.keyFontSize = Math.max(keyboard.settings.keyFontSize, Math.round(keyboard.settings.keyWidth / 2.5));
}

function drawKey(k, ctx) {
    var fs = keyboard.settings.keyFontSize
      , sc = keyboard.settings.keyStrokeColor
      , fc = keyboard.settings.keyFillColor
      , tc = keyboard.settings.keyTextColor
      ;

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

//    var center = getKeyCenter(k);

//    // DEBUG: Draw key centers.
//    ctx.beginPath();
//    ctx.fillStyle = 'red';
//    radius = keyboard.settings.keyFontSize;
//    ctx.arc(center.x, center.y, radius, 0, 2 * Math.PI);
//    ctx.fill();

//    // DEBUG: Draw key hitTarget areas.
//    ctx.beginPath();
//    ctx.strokeStyle = 'teal';
//    radius = 1.5 * Math.max(keyboard.settings.keyWidth, keyboard.settings.keyHeight) / 2;
//    ctx.arc(center.x, center.y, radius, 0, 2 * Math.PI);
//    ctx.stroke();

    // Draw key text.
    ctx.font = 'bold ' + fs + 'px "Arial"';
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

function renderKeyboard(keyboardCanvas) {
    var layoutSize = keyboard.measure(keyboardIndex);

    // Fill in all available space, according to the actual keyboard layout size.
    keyboardCanvas.width = layoutSize.width;
    keyboardCanvas.height = layoutSize.height;

//    // We can use a smaller area for the keyboard, in which case we should center it.
//    keyboardCanvas.width = containerSize.width;
//    keyboardCanvas.height = containerSize.height;
//    keyboard.settings.offsetLeft = (containerSize.width - layoutSize.width)/2;
//    keyboard.settings.offsetTop = (containerSize.height - layoutSize.height)/2;

    var ctx = keyboardCanvas.getContext('2d');

    // Draw background color.
    ctx.fillStyle = keyboard.settings.backgroundColor;
    ctx.fillRect(0, 0, keyboardCanvas.width, keyboardCanvas.height);
    // Draw keys.
    keyboard.getLayout(keyboardIndex).forEach(k => drawKey(k, ctx));

//    // DEBUG: Draw keyboard bounding box, to ensure we got the layout measures right.
//    ctx.strokeStyle = 'green';
//    ctx.rect(keyboard.settings.offsetLeft, keyboard.settings.offsetTop, layoutSize.width, layoutSize.height);
//    ctx.stroke();
}

function getKeyCenter(k) {
    return {
        x: (k.x + k.width/2)
      , y: (k.y + k.height/2)
    };
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
