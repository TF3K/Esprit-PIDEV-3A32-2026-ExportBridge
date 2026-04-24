'use strict'

const pc = require('picocolors')

const colors = {
  success: 'green',
  info: 'blue',
  note: 'white',
  warn: 'yellow',
  error: 'red'
}

const titles = {
  success: 'success',
  info: 'info',
  note: 'note',
  warn: 'warning',
  error: 'error'
}

const bgColorFns = {
  green: pc.bgGreen,
  blue: pc.bgBlue,
  white: pc.bgWhite,
  yellow: pc.bgYellow,
  red: pc.bgRed
}

const textColorFns = {
  green: pc.green,
  blue: pc.blue,
  white: pc.white,
  yellow: pc.yellow,
  red: pc.red
}

function bgColor (level) {
  const color = textColor(level)
  return bgColorFns[color] || pc.bgRed
}

function textColor (level) {
  return colors[level.toLowerCase()] || 'red'
}

function formatTitle (severity, title) {
  const bgFn = bgColor(severity)
  return bgFn(pc.black(` ${title} `))
}

function formatText (severity, message) {
  const colorFn = textColorFns[textColor(severity)] || pc.red
  return colorFn(message)
}

function clearConsole () {
  process.stdout.write(
    process.platform === 'win32' ? '\x1B[2J\x1B[0f' : '\x1B[2J\x1B[3J\x1B[H'
  )
}

module.exports = {
  colors,
  titles,
  formatText,
  formatTitle,
  clearConsole
}
