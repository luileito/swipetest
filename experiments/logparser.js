#!/usr/bin/env node

var fs    = require("fs")
  , path  = require("path")
  , csv   = require("fast-csv")
  ;

function is_flag(val) {
    return val === '0' || val === '1';
}

function load(filename) {
    var csvContent = [];
    return new Promise(function(resolve, reject) {
        fs.createReadStream(filename)
          .pipe(csv.parse({ headers: false, delimiter: ' ' }))
          .transform(row => {
              // Skip ill-formed logs.
              if (row.length < 12 || !is_flag(row[11])) {
                  // continue
              } else {
                  return {
                      timestamp: parseInt(row[1]),
                      canvasWidth: parseInt(row[2]),
                      canvasHeight: parseInt(row[3]),
                      event: row[4],
                      x: parseInt(row[5]),
                      y: parseInt(row[6]),
                      word: row[10],
                      isFailedWord: row[11] === '1',
                  }
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
