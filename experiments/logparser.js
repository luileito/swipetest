#!/usr/bin/env node

var fs    = require("fs")
  , path  = require("path")
  , csv   = require("fast-csv")
  ;

function load(filename) {
    var oldHeaders = ['sentenceHash', 'timestamp', 'canvasWidth', 'canvasHeight', 'event', 'x', 'y', 'word', 'isFailedWord'];
    var newHeaders = ['sentenceHash', 'timestamp', 'canvasWidth', 'canvasHeight', 'event', 'x', 'y', 'rx', 'ry', 'angle', 'word', 'isFailedWord'];
    var csvContent = [];
    return new Promise(function(resolve, reject) {
        fs.createReadStream(filename)
          .pipe(csv.parse({ headers: false, delimiter: ' ' }))
          .transform(data => {
              if (data.length === oldHeaders.length) {
                  // Old format. DEPRECATED!
                  return {
                      timestamp: parseInt(data[1]),
                      canvasWidth: parseInt(data[2]),
                      canvasHeight: parseInt(data[3]),
                      event: data[4],
                      x: parseInt(data[5]),
                      y: parseInt(data[6]),
                      word: data[7],
                      isFailedWord: data[data.length - 1] === '1',
                  }
              } else if (data.length >= newHeaders.length) {
                  // New format.
                  return {
                      timestamp: parseInt(data[1]),
                      canvasWidth: parseInt(data[2]),
                      canvasHeight: parseInt(data[3]),
                      event: data[4],
                      x: parseInt(data[5]),
                      y: parseInt(data[6]),
                      word: data[10],
                      isFailedWord: data[data.length - 1] === '1',
                  }
              } else {
                  console.error('Unsupported row format: %d columns.', data.length);
                  process.exit(1);
              }
          })
          .on('data', row => {
              csvContent.push(row);
          })
          .on('error', reject)
          .on('end', function() {
              resolve(csvContent);
          });
      });
}

// Public API.
module.exports = {
    load: load,
};
