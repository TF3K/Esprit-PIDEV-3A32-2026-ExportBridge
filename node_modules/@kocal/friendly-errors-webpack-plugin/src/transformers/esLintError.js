'use strict'

function isEslintError (e) {
  return e.originalStack
    .some(stackframe => {
      if (!stackframe.fileName) return false
      // Support both legacy eslint-loader and modern eslint-webpack-plugin
      return stackframe.fileName.indexOf('eslint-loader') > 0 ||
        stackframe.fileName.indexOf('eslint-webpack-plugin') > 0
    })
}

function transform (error) {
  if (isEslintError(error)) {
    return Object.assign({}, error, {
      name: 'Lint error',
      type: 'lint-error'
    })
  }

  return error
}

module.exports = transform
