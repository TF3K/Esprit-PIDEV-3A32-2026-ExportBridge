'use strict'

const TYPE = 'module-not-found'

function isModuleNotFoundError (e) {
  return e.name === 'ModuleNotFoundError' && e.message.indexOf('Module not found') === 0
}

function extractModuleFromMessage (message) {
  // Extract module name from "Module not found: Error: Can't resolve 'module-name'"
  const match = message.match(/Can't resolve '([^']+)'/)
  return match ? match[1] : null
}

function transform (error) {
  if (isModuleNotFoundError(error)) {
    const module = extractModuleFromMessage(error.message)

    if (module) {
      return Object.assign({}, error, {
        message: `Module not found ${module}`,
        type: TYPE,
        severity: 900,
        module,
        name: 'Module not found'
      })
    }
  }

  return error
}

module.exports = transform
