/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/assertThisInitialized.js ***!
  \**********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

module.exports = _assertThisInitialized;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/classCallCheck.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/classCallCheck.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

module.exports = _classCallCheck;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/createClass.js":
/*!************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/createClass.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

module.exports = _createClass;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/defineProperty.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/defineProperty.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

module.exports = _defineProperty;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/getPrototypeOf.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _getPrototypeOf(o) {
  module.exports = _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

module.exports = _getPrototypeOf;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/inherits.js":
/*!*********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/inherits.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var setPrototypeOf = __webpack_require__(/*! ./setPrototypeOf */ "./node_modules/@babel/runtime/helpers/setPrototypeOf.js");

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) setPrototypeOf(subClass, superClass);
}

module.exports = _inherits;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js":
/*!**************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js ***!
  \**************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var _typeof = __webpack_require__(/*! ../helpers/typeof */ "./node_modules/@babel/runtime/helpers/typeof.js");

var assertThisInitialized = __webpack_require__(/*! ./assertThisInitialized */ "./node_modules/@babel/runtime/helpers/assertThisInitialized.js");

function _possibleConstructorReturn(self, call) {
  if (call && (_typeof(call) === "object" || typeof call === "function")) {
    return call;
  }

  return assertThisInitialized(self);
}

module.exports = _possibleConstructorReturn;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/setPrototypeOf.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/setPrototypeOf.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _setPrototypeOf(o, p) {
  module.exports = _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

module.exports = _setPrototypeOf;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/typeof.js":
/*!*******************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/typeof.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _typeof(obj) {
  "@babel/helpers - typeof";

  if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
    module.exports = _typeof = function _typeof(obj) {
      return typeof obj;
    };
  } else {
    module.exports = _typeof = function _typeof(obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };
  }

  return _typeof(obj);
}

module.exports = _typeof;

/***/ }),

/***/ "./node_modules/object-assign/index.js":
/*!*********************************************!*\
  !*** ./node_modules/object-assign/index.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
object-assign
(c) Sindre Sorhus
@license MIT
*/


/* eslint-disable no-unused-vars */
var getOwnPropertySymbols = Object.getOwnPropertySymbols;
var hasOwnProperty = Object.prototype.hasOwnProperty;
var propIsEnumerable = Object.prototype.propertyIsEnumerable;

function toObject(val) {
	if (val === null || val === undefined) {
		throw new TypeError('Object.assign cannot be called with null or undefined');
	}

	return Object(val);
}

function shouldUseNative() {
	try {
		if (!Object.assign) {
			return false;
		}

		// Detect buggy property enumeration order in older V8 versions.

		// https://bugs.chromium.org/p/v8/issues/detail?id=4118
		var test1 = new String('abc');  // eslint-disable-line no-new-wrappers
		test1[5] = 'de';
		if (Object.getOwnPropertyNames(test1)[0] === '5') {
			return false;
		}

		// https://bugs.chromium.org/p/v8/issues/detail?id=3056
		var test2 = {};
		for (var i = 0; i < 10; i++) {
			test2['_' + String.fromCharCode(i)] = i;
		}
		var order2 = Object.getOwnPropertyNames(test2).map(function (n) {
			return test2[n];
		});
		if (order2.join('') !== '0123456789') {
			return false;
		}

		// https://bugs.chromium.org/p/v8/issues/detail?id=3056
		var test3 = {};
		'abcdefghijklmnopqrst'.split('').forEach(function (letter) {
			test3[letter] = letter;
		});
		if (Object.keys(Object.assign({}, test3)).join('') !==
				'abcdefghijklmnopqrst') {
			return false;
		}

		return true;
	} catch (err) {
		// We don't expect any of the above to throw, but better to be safe.
		return false;
	}
}

module.exports = shouldUseNative() ? Object.assign : function (target, source) {
	var from;
	var to = toObject(target);
	var symbols;

	for (var s = 1; s < arguments.length; s++) {
		from = Object(arguments[s]);

		for (var key in from) {
			if (hasOwnProperty.call(from, key)) {
				to[key] = from[key];
			}
		}

		if (getOwnPropertySymbols) {
			symbols = getOwnPropertySymbols(from);
			for (var i = 0; i < symbols.length; i++) {
				if (propIsEnumerable.call(from, symbols[i])) {
					to[symbols[i]] = from[symbols[i]];
				}
			}
		}
	}

	return to;
};


/***/ }),

/***/ "./node_modules/prop-types/checkPropTypes.js":
/*!***************************************************!*\
  !*** ./node_modules/prop-types/checkPropTypes.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var printWarning = function() {};

if (true) {
  var ReactPropTypesSecret = __webpack_require__(/*! ./lib/ReactPropTypesSecret */ "./node_modules/prop-types/lib/ReactPropTypesSecret.js");
  var loggedTypeFailures = {};
  var has = Function.call.bind(Object.prototype.hasOwnProperty);

  printWarning = function(text) {
    var message = 'Warning: ' + text;
    if (typeof console !== 'undefined') {
      console.error(message);
    }
    try {
      // --- Welcome to debugging React ---
      // This error was thrown as a convenience so that you can use this stack
      // to find the callsite that caused this warning to fire.
      throw new Error(message);
    } catch (x) {}
  };
}

/**
 * Assert that the values match with the type specs.
 * Error messages are memorized and will only be shown once.
 *
 * @param {object} typeSpecs Map of name to a ReactPropType
 * @param {object} values Runtime values that need to be type-checked
 * @param {string} location e.g. "prop", "context", "child context"
 * @param {string} componentName Name of the component for error messages.
 * @param {?Function} getStack Returns the component stack.
 * @private
 */
function checkPropTypes(typeSpecs, values, location, componentName, getStack) {
  if (true) {
    for (var typeSpecName in typeSpecs) {
      if (has(typeSpecs, typeSpecName)) {
        var error;
        // Prop type validation may throw. In case they do, we don't want to
        // fail the render phase where it didn't fail before. So we log it.
        // After these have been cleaned up, we'll let them throw.
        try {
          // This is intentionally an invariant that gets caught. It's the same
          // behavior as without this statement except with a better message.
          if (typeof typeSpecs[typeSpecName] !== 'function') {
            var err = Error(
              (componentName || 'React class') + ': ' + location + ' type `' + typeSpecName + '` is invalid; ' +
              'it must be a function, usually from the `prop-types` package, but received `' + typeof typeSpecs[typeSpecName] + '`.'
            );
            err.name = 'Invariant Violation';
            throw err;
          }
          error = typeSpecs[typeSpecName](values, typeSpecName, componentName, location, null, ReactPropTypesSecret);
        } catch (ex) {
          error = ex;
        }
        if (error && !(error instanceof Error)) {
          printWarning(
            (componentName || 'React class') + ': type specification of ' +
            location + ' `' + typeSpecName + '` is invalid; the type checker ' +
            'function must return `null` or an `Error` but returned a ' + typeof error + '. ' +
            'You may have forgotten to pass an argument to the type checker ' +
            'creator (arrayOf, instanceOf, objectOf, oneOf, oneOfType, and ' +
            'shape all require an argument).'
          );
        }
        if (error instanceof Error && !(error.message in loggedTypeFailures)) {
          // Only monitor this failure once because there tends to be a lot of the
          // same error.
          loggedTypeFailures[error.message] = true;

          var stack = getStack ? getStack() : '';

          printWarning(
            'Failed ' + location + ' type: ' + error.message + (stack != null ? stack : '')
          );
        }
      }
    }
  }
}

/**
 * Resets warning cache when testing.
 *
 * @private
 */
checkPropTypes.resetWarningCache = function() {
  if (true) {
    loggedTypeFailures = {};
  }
}

module.exports = checkPropTypes;


/***/ }),

/***/ "./node_modules/prop-types/factoryWithTypeCheckers.js":
/*!************************************************************!*\
  !*** ./node_modules/prop-types/factoryWithTypeCheckers.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var ReactIs = __webpack_require__(/*! react-is */ "./node_modules/react-is/index.js");
var assign = __webpack_require__(/*! object-assign */ "./node_modules/object-assign/index.js");

var ReactPropTypesSecret = __webpack_require__(/*! ./lib/ReactPropTypesSecret */ "./node_modules/prop-types/lib/ReactPropTypesSecret.js");
var checkPropTypes = __webpack_require__(/*! ./checkPropTypes */ "./node_modules/prop-types/checkPropTypes.js");

var has = Function.call.bind(Object.prototype.hasOwnProperty);
var printWarning = function() {};

if (true) {
  printWarning = function(text) {
    var message = 'Warning: ' + text;
    if (typeof console !== 'undefined') {
      console.error(message);
    }
    try {
      // --- Welcome to debugging React ---
      // This error was thrown as a convenience so that you can use this stack
      // to find the callsite that caused this warning to fire.
      throw new Error(message);
    } catch (x) {}
  };
}

function emptyFunctionThatReturnsNull() {
  return null;
}

module.exports = function(isValidElement, throwOnDirectAccess) {
  /* global Symbol */
  var ITERATOR_SYMBOL = typeof Symbol === 'function' && Symbol.iterator;
  var FAUX_ITERATOR_SYMBOL = '@@iterator'; // Before Symbol spec.

  /**
   * Returns the iterator method function contained on the iterable object.
   *
   * Be sure to invoke the function with the iterable as context:
   *
   *     var iteratorFn = getIteratorFn(myIterable);
   *     if (iteratorFn) {
   *       var iterator = iteratorFn.call(myIterable);
   *       ...
   *     }
   *
   * @param {?object} maybeIterable
   * @return {?function}
   */
  function getIteratorFn(maybeIterable) {
    var iteratorFn = maybeIterable && (ITERATOR_SYMBOL && maybeIterable[ITERATOR_SYMBOL] || maybeIterable[FAUX_ITERATOR_SYMBOL]);
    if (typeof iteratorFn === 'function') {
      return iteratorFn;
    }
  }

  /**
   * Collection of methods that allow declaration and validation of props that are
   * supplied to React components. Example usage:
   *
   *   var Props = require('ReactPropTypes');
   *   var MyArticle = React.createClass({
   *     propTypes: {
   *       // An optional string prop named "description".
   *       description: Props.string,
   *
   *       // A required enum prop named "category".
   *       category: Props.oneOf(['News','Photos']).isRequired,
   *
   *       // A prop named "dialog" that requires an instance of Dialog.
   *       dialog: Props.instanceOf(Dialog).isRequired
   *     },
   *     render: function() { ... }
   *   });
   *
   * A more formal specification of how these methods are used:
   *
   *   type := array|bool|func|object|number|string|oneOf([...])|instanceOf(...)
   *   decl := ReactPropTypes.{type}(.isRequired)?
   *
   * Each and every declaration produces a function with the same signature. This
   * allows the creation of custom validation functions. For example:
   *
   *  var MyLink = React.createClass({
   *    propTypes: {
   *      // An optional string or URI prop named "href".
   *      href: function(props, propName, componentName) {
   *        var propValue = props[propName];
   *        if (propValue != null && typeof propValue !== 'string' &&
   *            !(propValue instanceof URI)) {
   *          return new Error(
   *            'Expected a string or an URI for ' + propName + ' in ' +
   *            componentName
   *          );
   *        }
   *      }
   *    },
   *    render: function() {...}
   *  });
   *
   * @internal
   */

  var ANONYMOUS = '<<anonymous>>';

  // Important!
  // Keep this list in sync with production version in `./factoryWithThrowingShims.js`.
  var ReactPropTypes = {
    array: createPrimitiveTypeChecker('array'),
    bool: createPrimitiveTypeChecker('boolean'),
    func: createPrimitiveTypeChecker('function'),
    number: createPrimitiveTypeChecker('number'),
    object: createPrimitiveTypeChecker('object'),
    string: createPrimitiveTypeChecker('string'),
    symbol: createPrimitiveTypeChecker('symbol'),

    any: createAnyTypeChecker(),
    arrayOf: createArrayOfTypeChecker,
    element: createElementTypeChecker(),
    elementType: createElementTypeTypeChecker(),
    instanceOf: createInstanceTypeChecker,
    node: createNodeChecker(),
    objectOf: createObjectOfTypeChecker,
    oneOf: createEnumTypeChecker,
    oneOfType: createUnionTypeChecker,
    shape: createShapeTypeChecker,
    exact: createStrictShapeTypeChecker,
  };

  /**
   * inlined Object.is polyfill to avoid requiring consumers ship their own
   * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/is
   */
  /*eslint-disable no-self-compare*/
  function is(x, y) {
    // SameValue algorithm
    if (x === y) {
      // Steps 1-5, 7-10
      // Steps 6.b-6.e: +0 != -0
      return x !== 0 || 1 / x === 1 / y;
    } else {
      // Step 6.a: NaN == NaN
      return x !== x && y !== y;
    }
  }
  /*eslint-enable no-self-compare*/

  /**
   * We use an Error-like object for backward compatibility as people may call
   * PropTypes directly and inspect their output. However, we don't use real
   * Errors anymore. We don't inspect their stack anyway, and creating them
   * is prohibitively expensive if they are created too often, such as what
   * happens in oneOfType() for any type before the one that matched.
   */
  function PropTypeError(message) {
    this.message = message;
    this.stack = '';
  }
  // Make `instanceof Error` still work for returned errors.
  PropTypeError.prototype = Error.prototype;

  function createChainableTypeChecker(validate) {
    if (true) {
      var manualPropTypeCallCache = {};
      var manualPropTypeWarningCount = 0;
    }
    function checkType(isRequired, props, propName, componentName, location, propFullName, secret) {
      componentName = componentName || ANONYMOUS;
      propFullName = propFullName || propName;

      if (secret !== ReactPropTypesSecret) {
        if (throwOnDirectAccess) {
          // New behavior only for users of `prop-types` package
          var err = new Error(
            'Calling PropTypes validators directly is not supported by the `prop-types` package. ' +
            'Use `PropTypes.checkPropTypes()` to call them. ' +
            'Read more at http://fb.me/use-check-prop-types'
          );
          err.name = 'Invariant Violation';
          throw err;
        } else if ( true && typeof console !== 'undefined') {
          // Old behavior for people using React.PropTypes
          var cacheKey = componentName + ':' + propName;
          if (
            !manualPropTypeCallCache[cacheKey] &&
            // Avoid spamming the console because they are often not actionable except for lib authors
            manualPropTypeWarningCount < 3
          ) {
            printWarning(
              'You are manually calling a React.PropTypes validation ' +
              'function for the `' + propFullName + '` prop on `' + componentName  + '`. This is deprecated ' +
              'and will throw in the standalone `prop-types` package. ' +
              'You may be seeing this warning due to a third-party PropTypes ' +
              'library. See https://fb.me/react-warning-dont-call-proptypes ' + 'for details.'
            );
            manualPropTypeCallCache[cacheKey] = true;
            manualPropTypeWarningCount++;
          }
        }
      }
      if (props[propName] == null) {
        if (isRequired) {
          if (props[propName] === null) {
            return new PropTypeError('The ' + location + ' `' + propFullName + '` is marked as required ' + ('in `' + componentName + '`, but its value is `null`.'));
          }
          return new PropTypeError('The ' + location + ' `' + propFullName + '` is marked as required in ' + ('`' + componentName + '`, but its value is `undefined`.'));
        }
        return null;
      } else {
        return validate(props, propName, componentName, location, propFullName);
      }
    }

    var chainedCheckType = checkType.bind(null, false);
    chainedCheckType.isRequired = checkType.bind(null, true);

    return chainedCheckType;
  }

  function createPrimitiveTypeChecker(expectedType) {
    function validate(props, propName, componentName, location, propFullName, secret) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== expectedType) {
        // `propValue` being instance of, say, date/regexp, pass the 'object'
        // check, but we can offer a more precise error message here rather than
        // 'of type `object`'.
        var preciseType = getPreciseType(propValue);

        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + preciseType + '` supplied to `' + componentName + '`, expected ') + ('`' + expectedType + '`.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createAnyTypeChecker() {
    return createChainableTypeChecker(emptyFunctionThatReturnsNull);
  }

  function createArrayOfTypeChecker(typeChecker) {
    function validate(props, propName, componentName, location, propFullName) {
      if (typeof typeChecker !== 'function') {
        return new PropTypeError('Property `' + propFullName + '` of component `' + componentName + '` has invalid PropType notation inside arrayOf.');
      }
      var propValue = props[propName];
      if (!Array.isArray(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected an array.'));
      }
      for (var i = 0; i < propValue.length; i++) {
        var error = typeChecker(propValue, i, componentName, location, propFullName + '[' + i + ']', ReactPropTypesSecret);
        if (error instanceof Error) {
          return error;
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createElementTypeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      if (!isValidElement(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected a single ReactElement.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createElementTypeTypeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      if (!ReactIs.isValidElementType(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected a single ReactElement type.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createInstanceTypeChecker(expectedClass) {
    function validate(props, propName, componentName, location, propFullName) {
      if (!(props[propName] instanceof expectedClass)) {
        var expectedClassName = expectedClass.name || ANONYMOUS;
        var actualClassName = getClassName(props[propName]);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + actualClassName + '` supplied to `' + componentName + '`, expected ') + ('instance of `' + expectedClassName + '`.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createEnumTypeChecker(expectedValues) {
    if (!Array.isArray(expectedValues)) {
      if (true) {
        if (arguments.length > 1) {
          printWarning(
            'Invalid arguments supplied to oneOf, expected an array, got ' + arguments.length + ' arguments. ' +
            'A common mistake is to write oneOf(x, y, z) instead of oneOf([x, y, z]).'
          );
        } else {
          printWarning('Invalid argument supplied to oneOf, expected an array.');
        }
      }
      return emptyFunctionThatReturnsNull;
    }

    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      for (var i = 0; i < expectedValues.length; i++) {
        if (is(propValue, expectedValues[i])) {
          return null;
        }
      }

      var valuesString = JSON.stringify(expectedValues, function replacer(key, value) {
        var type = getPreciseType(value);
        if (type === 'symbol') {
          return String(value);
        }
        return value;
      });
      return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of value `' + String(propValue) + '` ' + ('supplied to `' + componentName + '`, expected one of ' + valuesString + '.'));
    }
    return createChainableTypeChecker(validate);
  }

  function createObjectOfTypeChecker(typeChecker) {
    function validate(props, propName, componentName, location, propFullName) {
      if (typeof typeChecker !== 'function') {
        return new PropTypeError('Property `' + propFullName + '` of component `' + componentName + '` has invalid PropType notation inside objectOf.');
      }
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected an object.'));
      }
      for (var key in propValue) {
        if (has(propValue, key)) {
          var error = typeChecker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
          if (error instanceof Error) {
            return error;
          }
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createUnionTypeChecker(arrayOfTypeCheckers) {
    if (!Array.isArray(arrayOfTypeCheckers)) {
       true ? printWarning('Invalid argument supplied to oneOfType, expected an instance of array.') : undefined;
      return emptyFunctionThatReturnsNull;
    }

    for (var i = 0; i < arrayOfTypeCheckers.length; i++) {
      var checker = arrayOfTypeCheckers[i];
      if (typeof checker !== 'function') {
        printWarning(
          'Invalid argument supplied to oneOfType. Expected an array of check functions, but ' +
          'received ' + getPostfixForTypeWarning(checker) + ' at index ' + i + '.'
        );
        return emptyFunctionThatReturnsNull;
      }
    }

    function validate(props, propName, componentName, location, propFullName) {
      for (var i = 0; i < arrayOfTypeCheckers.length; i++) {
        var checker = arrayOfTypeCheckers[i];
        if (checker(props, propName, componentName, location, propFullName, ReactPropTypesSecret) == null) {
          return null;
        }
      }

      return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` supplied to ' + ('`' + componentName + '`.'));
    }
    return createChainableTypeChecker(validate);
  }

  function createNodeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      if (!isNode(props[propName])) {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` supplied to ' + ('`' + componentName + '`, expected a ReactNode.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createShapeTypeChecker(shapeTypes) {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type `' + propType + '` ' + ('supplied to `' + componentName + '`, expected `object`.'));
      }
      for (var key in shapeTypes) {
        var checker = shapeTypes[key];
        if (!checker) {
          continue;
        }
        var error = checker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
        if (error) {
          return error;
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createStrictShapeTypeChecker(shapeTypes) {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type `' + propType + '` ' + ('supplied to `' + componentName + '`, expected `object`.'));
      }
      // We need to check all keys in case some are required but missing from
      // props.
      var allKeys = assign({}, props[propName], shapeTypes);
      for (var key in allKeys) {
        var checker = shapeTypes[key];
        if (!checker) {
          return new PropTypeError(
            'Invalid ' + location + ' `' + propFullName + '` key `' + key + '` supplied to `' + componentName + '`.' +
            '\nBad object: ' + JSON.stringify(props[propName], null, '  ') +
            '\nValid keys: ' +  JSON.stringify(Object.keys(shapeTypes), null, '  ')
          );
        }
        var error = checker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
        if (error) {
          return error;
        }
      }
      return null;
    }

    return createChainableTypeChecker(validate);
  }

  function isNode(propValue) {
    switch (typeof propValue) {
      case 'number':
      case 'string':
      case 'undefined':
        return true;
      case 'boolean':
        return !propValue;
      case 'object':
        if (Array.isArray(propValue)) {
          return propValue.every(isNode);
        }
        if (propValue === null || isValidElement(propValue)) {
          return true;
        }

        var iteratorFn = getIteratorFn(propValue);
        if (iteratorFn) {
          var iterator = iteratorFn.call(propValue);
          var step;
          if (iteratorFn !== propValue.entries) {
            while (!(step = iterator.next()).done) {
              if (!isNode(step.value)) {
                return false;
              }
            }
          } else {
            // Iterator will provide entry [k,v] tuples rather than values.
            while (!(step = iterator.next()).done) {
              var entry = step.value;
              if (entry) {
                if (!isNode(entry[1])) {
                  return false;
                }
              }
            }
          }
        } else {
          return false;
        }

        return true;
      default:
        return false;
    }
  }

  function isSymbol(propType, propValue) {
    // Native Symbol.
    if (propType === 'symbol') {
      return true;
    }

    // falsy value can't be a Symbol
    if (!propValue) {
      return false;
    }

    // 19.4.3.5 Symbol.prototype[@@toStringTag] === 'Symbol'
    if (propValue['@@toStringTag'] === 'Symbol') {
      return true;
    }

    // Fallback for non-spec compliant Symbols which are polyfilled.
    if (typeof Symbol === 'function' && propValue instanceof Symbol) {
      return true;
    }

    return false;
  }

  // Equivalent of `typeof` but with special handling for array and regexp.
  function getPropType(propValue) {
    var propType = typeof propValue;
    if (Array.isArray(propValue)) {
      return 'array';
    }
    if (propValue instanceof RegExp) {
      // Old webkits (at least until Android 4.0) return 'function' rather than
      // 'object' for typeof a RegExp. We'll normalize this here so that /bla/
      // passes PropTypes.object.
      return 'object';
    }
    if (isSymbol(propType, propValue)) {
      return 'symbol';
    }
    return propType;
  }

  // This handles more types than `getPropType`. Only used for error messages.
  // See `createPrimitiveTypeChecker`.
  function getPreciseType(propValue) {
    if (typeof propValue === 'undefined' || propValue === null) {
      return '' + propValue;
    }
    var propType = getPropType(propValue);
    if (propType === 'object') {
      if (propValue instanceof Date) {
        return 'date';
      } else if (propValue instanceof RegExp) {
        return 'regexp';
      }
    }
    return propType;
  }

  // Returns a string that is postfixed to a warning about an invalid type.
  // For example, "undefined" or "of type array"
  function getPostfixForTypeWarning(value) {
    var type = getPreciseType(value);
    switch (type) {
      case 'array':
      case 'object':
        return 'an ' + type;
      case 'boolean':
      case 'date':
      case 'regexp':
        return 'a ' + type;
      default:
        return type;
    }
  }

  // Returns class name of the object, if any.
  function getClassName(propValue) {
    if (!propValue.constructor || !propValue.constructor.name) {
      return ANONYMOUS;
    }
    return propValue.constructor.name;
  }

  ReactPropTypes.checkPropTypes = checkPropTypes;
  ReactPropTypes.resetWarningCache = checkPropTypes.resetWarningCache;
  ReactPropTypes.PropTypes = ReactPropTypes;

  return ReactPropTypes;
};


/***/ }),

/***/ "./node_modules/prop-types/index.js":
/*!******************************************!*\
  !*** ./node_modules/prop-types/index.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

if (true) {
  var ReactIs = __webpack_require__(/*! react-is */ "./node_modules/react-is/index.js");

  // By explicitly using `prop-types` you are opting into new development behavior.
  // http://fb.me/prop-types-in-prod
  var throwOnDirectAccess = true;
  module.exports = __webpack_require__(/*! ./factoryWithTypeCheckers */ "./node_modules/prop-types/factoryWithTypeCheckers.js")(ReactIs.isElement, throwOnDirectAccess);
} else {}


/***/ }),

/***/ "./node_modules/prop-types/lib/ReactPropTypesSecret.js":
/*!*************************************************************!*\
  !*** ./node_modules/prop-types/lib/ReactPropTypesSecret.js ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var ReactPropTypesSecret = 'SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED';

module.exports = ReactPropTypesSecret;


/***/ }),

/***/ "./node_modules/react-is/cjs/react-is.development.js":
/*!***********************************************************!*\
  !*** ./node_modules/react-is/cjs/react-is.development.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/** @license React v16.13.1
 * react-is.development.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */





if (true) {
  (function() {
'use strict';

// The Symbol used to tag the ReactElement-like types. If there is no native Symbol
// nor polyfill, then a plain number is used for performance.
var hasSymbol = typeof Symbol === 'function' && Symbol.for;
var REACT_ELEMENT_TYPE = hasSymbol ? Symbol.for('react.element') : 0xeac7;
var REACT_PORTAL_TYPE = hasSymbol ? Symbol.for('react.portal') : 0xeaca;
var REACT_FRAGMENT_TYPE = hasSymbol ? Symbol.for('react.fragment') : 0xeacb;
var REACT_STRICT_MODE_TYPE = hasSymbol ? Symbol.for('react.strict_mode') : 0xeacc;
var REACT_PROFILER_TYPE = hasSymbol ? Symbol.for('react.profiler') : 0xead2;
var REACT_PROVIDER_TYPE = hasSymbol ? Symbol.for('react.provider') : 0xeacd;
var REACT_CONTEXT_TYPE = hasSymbol ? Symbol.for('react.context') : 0xeace; // TODO: We don't use AsyncMode or ConcurrentMode anymore. They were temporary
// (unstable) APIs that have been removed. Can we remove the symbols?

var REACT_ASYNC_MODE_TYPE = hasSymbol ? Symbol.for('react.async_mode') : 0xeacf;
var REACT_CONCURRENT_MODE_TYPE = hasSymbol ? Symbol.for('react.concurrent_mode') : 0xeacf;
var REACT_FORWARD_REF_TYPE = hasSymbol ? Symbol.for('react.forward_ref') : 0xead0;
var REACT_SUSPENSE_TYPE = hasSymbol ? Symbol.for('react.suspense') : 0xead1;
var REACT_SUSPENSE_LIST_TYPE = hasSymbol ? Symbol.for('react.suspense_list') : 0xead8;
var REACT_MEMO_TYPE = hasSymbol ? Symbol.for('react.memo') : 0xead3;
var REACT_LAZY_TYPE = hasSymbol ? Symbol.for('react.lazy') : 0xead4;
var REACT_BLOCK_TYPE = hasSymbol ? Symbol.for('react.block') : 0xead9;
var REACT_FUNDAMENTAL_TYPE = hasSymbol ? Symbol.for('react.fundamental') : 0xead5;
var REACT_RESPONDER_TYPE = hasSymbol ? Symbol.for('react.responder') : 0xead6;
var REACT_SCOPE_TYPE = hasSymbol ? Symbol.for('react.scope') : 0xead7;

function isValidElementType(type) {
  return typeof type === 'string' || typeof type === 'function' || // Note: its typeof might be other than 'symbol' or 'number' if it's a polyfill.
  type === REACT_FRAGMENT_TYPE || type === REACT_CONCURRENT_MODE_TYPE || type === REACT_PROFILER_TYPE || type === REACT_STRICT_MODE_TYPE || type === REACT_SUSPENSE_TYPE || type === REACT_SUSPENSE_LIST_TYPE || typeof type === 'object' && type !== null && (type.$$typeof === REACT_LAZY_TYPE || type.$$typeof === REACT_MEMO_TYPE || type.$$typeof === REACT_PROVIDER_TYPE || type.$$typeof === REACT_CONTEXT_TYPE || type.$$typeof === REACT_FORWARD_REF_TYPE || type.$$typeof === REACT_FUNDAMENTAL_TYPE || type.$$typeof === REACT_RESPONDER_TYPE || type.$$typeof === REACT_SCOPE_TYPE || type.$$typeof === REACT_BLOCK_TYPE);
}

function typeOf(object) {
  if (typeof object === 'object' && object !== null) {
    var $$typeof = object.$$typeof;

    switch ($$typeof) {
      case REACT_ELEMENT_TYPE:
        var type = object.type;

        switch (type) {
          case REACT_ASYNC_MODE_TYPE:
          case REACT_CONCURRENT_MODE_TYPE:
          case REACT_FRAGMENT_TYPE:
          case REACT_PROFILER_TYPE:
          case REACT_STRICT_MODE_TYPE:
          case REACT_SUSPENSE_TYPE:
            return type;

          default:
            var $$typeofType = type && type.$$typeof;

            switch ($$typeofType) {
              case REACT_CONTEXT_TYPE:
              case REACT_FORWARD_REF_TYPE:
              case REACT_LAZY_TYPE:
              case REACT_MEMO_TYPE:
              case REACT_PROVIDER_TYPE:
                return $$typeofType;

              default:
                return $$typeof;
            }

        }

      case REACT_PORTAL_TYPE:
        return $$typeof;
    }
  }

  return undefined;
} // AsyncMode is deprecated along with isAsyncMode

var AsyncMode = REACT_ASYNC_MODE_TYPE;
var ConcurrentMode = REACT_CONCURRENT_MODE_TYPE;
var ContextConsumer = REACT_CONTEXT_TYPE;
var ContextProvider = REACT_PROVIDER_TYPE;
var Element = REACT_ELEMENT_TYPE;
var ForwardRef = REACT_FORWARD_REF_TYPE;
var Fragment = REACT_FRAGMENT_TYPE;
var Lazy = REACT_LAZY_TYPE;
var Memo = REACT_MEMO_TYPE;
var Portal = REACT_PORTAL_TYPE;
var Profiler = REACT_PROFILER_TYPE;
var StrictMode = REACT_STRICT_MODE_TYPE;
var Suspense = REACT_SUSPENSE_TYPE;
var hasWarnedAboutDeprecatedIsAsyncMode = false; // AsyncMode should be deprecated

function isAsyncMode(object) {
  {
    if (!hasWarnedAboutDeprecatedIsAsyncMode) {
      hasWarnedAboutDeprecatedIsAsyncMode = true; // Using console['warn'] to evade Babel and ESLint

      console['warn']('The ReactIs.isAsyncMode() alias has been deprecated, ' + 'and will be removed in React 17+. Update your code to use ' + 'ReactIs.isConcurrentMode() instead. It has the exact same API.');
    }
  }

  return isConcurrentMode(object) || typeOf(object) === REACT_ASYNC_MODE_TYPE;
}
function isConcurrentMode(object) {
  return typeOf(object) === REACT_CONCURRENT_MODE_TYPE;
}
function isContextConsumer(object) {
  return typeOf(object) === REACT_CONTEXT_TYPE;
}
function isContextProvider(object) {
  return typeOf(object) === REACT_PROVIDER_TYPE;
}
function isElement(object) {
  return typeof object === 'object' && object !== null && object.$$typeof === REACT_ELEMENT_TYPE;
}
function isForwardRef(object) {
  return typeOf(object) === REACT_FORWARD_REF_TYPE;
}
function isFragment(object) {
  return typeOf(object) === REACT_FRAGMENT_TYPE;
}
function isLazy(object) {
  return typeOf(object) === REACT_LAZY_TYPE;
}
function isMemo(object) {
  return typeOf(object) === REACT_MEMO_TYPE;
}
function isPortal(object) {
  return typeOf(object) === REACT_PORTAL_TYPE;
}
function isProfiler(object) {
  return typeOf(object) === REACT_PROFILER_TYPE;
}
function isStrictMode(object) {
  return typeOf(object) === REACT_STRICT_MODE_TYPE;
}
function isSuspense(object) {
  return typeOf(object) === REACT_SUSPENSE_TYPE;
}

exports.AsyncMode = AsyncMode;
exports.ConcurrentMode = ConcurrentMode;
exports.ContextConsumer = ContextConsumer;
exports.ContextProvider = ContextProvider;
exports.Element = Element;
exports.ForwardRef = ForwardRef;
exports.Fragment = Fragment;
exports.Lazy = Lazy;
exports.Memo = Memo;
exports.Portal = Portal;
exports.Profiler = Profiler;
exports.StrictMode = StrictMode;
exports.Suspense = Suspense;
exports.isAsyncMode = isAsyncMode;
exports.isConcurrentMode = isConcurrentMode;
exports.isContextConsumer = isContextConsumer;
exports.isContextProvider = isContextProvider;
exports.isElement = isElement;
exports.isForwardRef = isForwardRef;
exports.isFragment = isFragment;
exports.isLazy = isLazy;
exports.isMemo = isMemo;
exports.isPortal = isPortal;
exports.isProfiler = isProfiler;
exports.isStrictMode = isStrictMode;
exports.isSuspense = isSuspense;
exports.isValidElementType = isValidElementType;
exports.typeOf = typeOf;
  })();
}


/***/ }),

/***/ "./node_modules/react-is/index.js":
/*!****************************************!*\
  !*** ./node_modules/react-is/index.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


if (false) {} else {
  module.exports = __webpack_require__(/*! ./cjs/react-is.development.js */ "./node_modules/react-is/cjs/react-is.development.js");
}


/***/ }),

/***/ "./node_modules/react-paginate/dist/BreakView.js":
/*!*******************************************************!*\
  !*** ./node_modules/react-paginate/dist/BreakView.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _react = __webpack_require__(/*! react */ "./node_modules/react/index.js");

var _react2 = _interopRequireDefault(_react);

var _propTypes = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");

var _propTypes2 = _interopRequireDefault(_propTypes);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var BreakView = function BreakView(props) {
  var breakLabel = props.breakLabel,
      breakClassName = props.breakClassName,
      breakLinkClassName = props.breakLinkClassName,
      onClick = props.onClick;

  var className = breakClassName || 'break';

  return _react2.default.createElement(
    'li',
    { className: className },
    _react2.default.createElement(
      'a',
      {
        className: breakLinkClassName,
        onClick: onClick,
        role: 'button',
        tabIndex: '0',
        onKeyPress: onClick
      },
      breakLabel
    )
  );
};

BreakView.propTypes = {
  breakLabel: _propTypes2.default.oneOfType([_propTypes2.default.string, _propTypes2.default.node]),
  breakClassName: _propTypes2.default.string,
  breakLinkClassName: _propTypes2.default.string,
  onClick: _propTypes2.default.func.isRequired
};

exports.default = BreakView;
//# sourceMappingURL=BreakView.js.map

/***/ }),

/***/ "./node_modules/react-paginate/dist/PageView.js":
/*!******************************************************!*\
  !*** ./node_modules/react-paginate/dist/PageView.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _react = __webpack_require__(/*! react */ "./node_modules/react/index.js");

var _react2 = _interopRequireDefault(_react);

var _propTypes = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");

var _propTypes2 = _interopRequireDefault(_propTypes);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var PageView = function PageView(props) {
  var pageClassName = props.pageClassName;
  var pageLinkClassName = props.pageLinkClassName;

  var onClick = props.onClick;
  var href = props.href;
  var ariaLabel = props.ariaLabel || 'Page ' + props.page + (props.extraAriaContext ? ' ' + props.extraAriaContext : '');
  var ariaCurrent = null;

  if (props.selected) {
    ariaCurrent = 'page';

    ariaLabel = props.ariaLabel || 'Page ' + props.page + ' is your current page';

    if (typeof pageClassName !== 'undefined') {
      pageClassName = pageClassName + ' ' + props.activeClassName;
    } else {
      pageClassName = props.activeClassName;
    }

    if (typeof pageLinkClassName !== 'undefined') {
      if (typeof props.activeLinkClassName !== 'undefined') {
        pageLinkClassName = pageLinkClassName + ' ' + props.activeLinkClassName;
      }
    } else {
      pageLinkClassName = props.activeLinkClassName;
    }
  }

  return _react2.default.createElement(
    'li',
    { className: pageClassName },
    _react2.default.createElement(
      'a',
      {
        onClick: onClick,
        role: 'button',
        className: pageLinkClassName,
        href: href,
        tabIndex: '0',
        'aria-label': ariaLabel,
        'aria-current': ariaCurrent,
        onKeyPress: onClick
      },
      props.page
    )
  );
};

PageView.propTypes = {
  onClick: _propTypes2.default.func.isRequired,
  selected: _propTypes2.default.bool.isRequired,
  pageClassName: _propTypes2.default.string,
  pageLinkClassName: _propTypes2.default.string,
  activeClassName: _propTypes2.default.string,
  activeLinkClassName: _propTypes2.default.string,
  extraAriaContext: _propTypes2.default.string,
  href: _propTypes2.default.string,
  ariaLabel: _propTypes2.default.string,
  page: _propTypes2.default.number.isRequired
};

exports.default = PageView;
//# sourceMappingURL=PageView.js.map

/***/ }),

/***/ "./node_modules/react-paginate/dist/PaginationBoxView.js":
/*!***************************************************************!*\
  !*** ./node_modules/react-paginate/dist/PaginationBoxView.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(/*! react */ "./node_modules/react/index.js");

var _react2 = _interopRequireDefault(_react);

var _propTypes = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");

var _propTypes2 = _interopRequireDefault(_propTypes);

var _PageView = __webpack_require__(/*! ./PageView */ "./node_modules/react-paginate/dist/PageView.js");

var _PageView2 = _interopRequireDefault(_PageView);

var _BreakView = __webpack_require__(/*! ./BreakView */ "./node_modules/react-paginate/dist/BreakView.js");

var _BreakView2 = _interopRequireDefault(_BreakView);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var PaginationBoxView = function (_Component) {
  _inherits(PaginationBoxView, _Component);

  function PaginationBoxView(props) {
    _classCallCheck(this, PaginationBoxView);

    var _this = _possibleConstructorReturn(this, (PaginationBoxView.__proto__ || Object.getPrototypeOf(PaginationBoxView)).call(this, props));

    _this.handlePreviousPage = function (evt) {
      var selected = _this.state.selected;

      evt.preventDefault ? evt.preventDefault() : evt.returnValue = false;
      if (selected > 0) {
        _this.handlePageSelected(selected - 1, evt);
      }
    };

    _this.handleNextPage = function (evt) {
      var selected = _this.state.selected;
      var pageCount = _this.props.pageCount;


      evt.preventDefault ? evt.preventDefault() : evt.returnValue = false;
      if (selected < pageCount - 1) {
        _this.handlePageSelected(selected + 1, evt);
      }
    };

    _this.handlePageSelected = function (selected, evt) {
      evt.preventDefault ? evt.preventDefault() : evt.returnValue = false;

      if (_this.state.selected === selected) return;

      _this.setState({ selected: selected });

      // Call the callback with the new selected item:
      _this.callCallback(selected);
    };

    _this.handleBreakClick = function (index, evt) {
      evt.preventDefault ? evt.preventDefault() : evt.returnValue = false;

      var selected = _this.state.selected;


      _this.handlePageSelected(selected < index ? _this.getForwardJump() : _this.getBackwardJump(), evt);
    };

    _this.callCallback = function (selectedItem) {
      if (typeof _this.props.onPageChange !== 'undefined' && typeof _this.props.onPageChange === 'function') {
        _this.props.onPageChange({ selected: selectedItem });
      }
    };

    _this.pagination = function () {
      var items = [];
      var _this$props = _this.props,
          pageRangeDisplayed = _this$props.pageRangeDisplayed,
          pageCount = _this$props.pageCount,
          marginPagesDisplayed = _this$props.marginPagesDisplayed,
          breakLabel = _this$props.breakLabel,
          breakClassName = _this$props.breakClassName,
          breakLinkClassName = _this$props.breakLinkClassName;
      var selected = _this.state.selected;


      if (pageCount <= pageRangeDisplayed) {
        for (var index = 0; index < pageCount; index++) {
          items.push(_this.getPageElement(index));
        }
      } else {
        var leftSide = pageRangeDisplayed / 2;
        var rightSide = pageRangeDisplayed - leftSide;

        // If the selected page index is on the default right side of the pagination,
        // we consider that the new right side is made up of it (= only one break element).
        // If the selected page index is on the default left side of the pagination,
        // we consider that the new left side is made up of it (= only one break element).
        if (selected > pageCount - pageRangeDisplayed / 2) {
          rightSide = pageCount - selected;
          leftSide = pageRangeDisplayed - rightSide;
        } else if (selected < pageRangeDisplayed / 2) {
          leftSide = selected;
          rightSide = pageRangeDisplayed - leftSide;
        }

        var _index = void 0;
        var page = void 0;
        var breakView = void 0;
        var createPageView = function createPageView(index) {
          return _this.getPageElement(index);
        };

        for (_index = 0; _index < pageCount; _index++) {
          page = _index + 1;

          // If the page index is lower than the margin defined,
          // the page has to be displayed on the left side of
          // the pagination.
          if (page <= marginPagesDisplayed) {
            items.push(createPageView(_index));
            continue;
          }

          // If the page index is greater than the page count
          // minus the margin defined, the page has to be
          // displayed on the right side of the pagination.
          if (page > pageCount - marginPagesDisplayed) {
            items.push(createPageView(_index));
            continue;
          }

          // If the page index is near the selected page index
          // and inside the defined range (pageRangeDisplayed)
          // we have to display it (it will create the center
          // part of the pagination).
          if (_index >= selected - leftSide && _index <= selected + rightSide) {
            items.push(createPageView(_index));
            continue;
          }

          // If the page index doesn't meet any of the conditions above,
          // we check if the last item of the current "items" array
          // is a break element. If not, we add a break element, else,
          // we do nothing (because we don't want to display the page).
          if (breakLabel && items[items.length - 1] !== breakView) {
            breakView = _react2.default.createElement(_BreakView2.default, {
              key: _index,
              breakLabel: breakLabel,
              breakClassName: breakClassName,
              breakLinkClassName: breakLinkClassName,
              onClick: _this.handleBreakClick.bind(null, _index)
            });
            items.push(breakView);
          }
        }
      }

      return items;
    };

    var initialSelected = void 0;
    if (props.initialPage) {
      initialSelected = props.initialPage;
    } else if (props.forcePage) {
      initialSelected = props.forcePage;
    } else {
      initialSelected = 0;
    }

    _this.state = {
      selected: initialSelected
    };
    return _this;
  }

  _createClass(PaginationBoxView, [{
    key: 'componentDidMount',
    value: function componentDidMount() {
      var _props = this.props,
          initialPage = _props.initialPage,
          disableInitialCallback = _props.disableInitialCallback,
          extraAriaContext = _props.extraAriaContext;
      // Call the callback with the initialPage item:

      if (typeof initialPage !== 'undefined' && !disableInitialCallback) {
        this.callCallback(initialPage);
      }

      if (extraAriaContext) {
        console.warn('DEPRECATED (react-paginate): The extraAriaContext prop is deprecated. You should now use the ariaLabelBuilder instead.');
      }
    }
  }, {
    key: 'componentDidUpdate',
    value: function componentDidUpdate(prevProps) {
      if (typeof this.props.forcePage !== 'undefined' && this.props.forcePage !== prevProps.forcePage) {
        this.setState({ selected: this.props.forcePage });
      }
    }
  }, {
    key: 'getForwardJump',
    value: function getForwardJump() {
      var selected = this.state.selected;
      var _props2 = this.props,
          pageCount = _props2.pageCount,
          pageRangeDisplayed = _props2.pageRangeDisplayed;


      var forwardJump = selected + pageRangeDisplayed;
      return forwardJump >= pageCount ? pageCount - 1 : forwardJump;
    }
  }, {
    key: 'getBackwardJump',
    value: function getBackwardJump() {
      var selected = this.state.selected;
      var pageRangeDisplayed = this.props.pageRangeDisplayed;


      var backwardJump = selected - pageRangeDisplayed;
      return backwardJump < 0 ? 0 : backwardJump;
    }
  }, {
    key: 'hrefBuilder',
    value: function hrefBuilder(pageIndex) {
      var _props3 = this.props,
          hrefBuilder = _props3.hrefBuilder,
          pageCount = _props3.pageCount;

      if (hrefBuilder && pageIndex !== this.state.selected && pageIndex >= 0 && pageIndex < pageCount) {
        return hrefBuilder(pageIndex + 1);
      }
    }
  }, {
    key: 'ariaLabelBuilder',
    value: function ariaLabelBuilder(pageIndex) {
      var selected = pageIndex === this.state.selected;
      if (this.props.ariaLabelBuilder && pageIndex >= 0 && pageIndex < this.props.pageCount) {
        var label = this.props.ariaLabelBuilder(pageIndex + 1, selected);
        // DEPRECATED: The extraAriaContext prop was used to add additional context
        // to the aria-label. Users should now use the ariaLabelBuilder instead.
        if (this.props.extraAriaContext && !selected) {
          label = label + ' ' + this.props.extraAriaContext;
        }
        return label;
      }
    }
  }, {
    key: 'getPageElement',
    value: function getPageElement(index) {
      var selected = this.state.selected;
      var _props4 = this.props,
          pageClassName = _props4.pageClassName,
          pageLinkClassName = _props4.pageLinkClassName,
          activeClassName = _props4.activeClassName,
          activeLinkClassName = _props4.activeLinkClassName,
          extraAriaContext = _props4.extraAriaContext;


      return _react2.default.createElement(_PageView2.default, {
        key: index,
        onClick: this.handlePageSelected.bind(null, index),
        selected: selected === index,
        pageClassName: pageClassName,
        pageLinkClassName: pageLinkClassName,
        activeClassName: activeClassName,
        activeLinkClassName: activeLinkClassName,
        extraAriaContext: extraAriaContext,
        href: this.hrefBuilder(index),
        ariaLabel: this.ariaLabelBuilder(index),
        page: index + 1
      });
    }
  }, {
    key: 'render',
    value: function render() {
      var _props5 = this.props,
          disabledClassName = _props5.disabledClassName,
          previousClassName = _props5.previousClassName,
          nextClassName = _props5.nextClassName,
          pageCount = _props5.pageCount,
          containerClassName = _props5.containerClassName,
          previousLinkClassName = _props5.previousLinkClassName,
          previousLabel = _props5.previousLabel,
          nextLinkClassName = _props5.nextLinkClassName,
          nextLabel = _props5.nextLabel;
      var selected = this.state.selected;


      var previousClasses = previousClassName + (selected === 0 ? ' ' + disabledClassName : '');
      var nextClasses = nextClassName + (selected === pageCount - 1 ? ' ' + disabledClassName : '');

      var previousAriaDisabled = selected === 0 ? 'true' : 'false';
      var nextAriaDisabled = selected === pageCount - 1 ? 'true' : 'false';

      return _react2.default.createElement(
        'ul',
        { className: containerClassName },
        _react2.default.createElement(
          'li',
          { className: previousClasses },
          _react2.default.createElement(
            'a',
            {
              onClick: this.handlePreviousPage,
              className: previousLinkClassName,
              href: this.hrefBuilder(selected - 1),
              tabIndex: '0',
              role: 'button',
              onKeyPress: this.handlePreviousPage,
              'aria-disabled': previousAriaDisabled
            },
            previousLabel
          )
        ),
        this.pagination(),
        _react2.default.createElement(
          'li',
          { className: nextClasses },
          _react2.default.createElement(
            'a',
            {
              onClick: this.handleNextPage,
              className: nextLinkClassName,
              href: this.hrefBuilder(selected + 1),
              tabIndex: '0',
              role: 'button',
              onKeyPress: this.handleNextPage,
              'aria-disabled': nextAriaDisabled
            },
            nextLabel
          )
        )
      );
    }
  }]);

  return PaginationBoxView;
}(_react.Component);

PaginationBoxView.propTypes = {
  pageCount: _propTypes2.default.number.isRequired,
  pageRangeDisplayed: _propTypes2.default.number.isRequired,
  marginPagesDisplayed: _propTypes2.default.number.isRequired,
  previousLabel: _propTypes2.default.node,
  nextLabel: _propTypes2.default.node,
  breakLabel: _propTypes2.default.oneOfType([_propTypes2.default.string, _propTypes2.default.node]),
  hrefBuilder: _propTypes2.default.func,
  onPageChange: _propTypes2.default.func,
  initialPage: _propTypes2.default.number,
  forcePage: _propTypes2.default.number,
  disableInitialCallback: _propTypes2.default.bool,
  containerClassName: _propTypes2.default.string,
  pageClassName: _propTypes2.default.string,
  pageLinkClassName: _propTypes2.default.string,
  activeClassName: _propTypes2.default.string,
  activeLinkClassName: _propTypes2.default.string,
  previousClassName: _propTypes2.default.string,
  nextClassName: _propTypes2.default.string,
  previousLinkClassName: _propTypes2.default.string,
  nextLinkClassName: _propTypes2.default.string,
  disabledClassName: _propTypes2.default.string,
  breakClassName: _propTypes2.default.string,
  breakLinkClassName: _propTypes2.default.string,
  extraAriaContext: _propTypes2.default.string,
  ariaLabelBuilder: _propTypes2.default.func
};
PaginationBoxView.defaultProps = {
  pageCount: 10,
  pageRangeDisplayed: 2,
  marginPagesDisplayed: 3,
  activeClassName: 'selected',
  previousClassName: 'previous',
  nextClassName: 'next',
  previousLabel: 'Previous',
  nextLabel: 'Next',
  breakLabel: '...',
  disabledClassName: 'disabled',
  disableInitialCallback: false
};
exports.default = PaginationBoxView;
//# sourceMappingURL=PaginationBoxView.js.map

/***/ }),

/***/ "./node_modules/react-paginate/dist/index.js":
/*!***************************************************!*\
  !*** ./node_modules/react-paginate/dist/index.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _PaginationBoxView = __webpack_require__(/*! ./PaginationBoxView */ "./node_modules/react-paginate/dist/PaginationBoxView.js");

var _PaginationBoxView2 = _interopRequireDefault(_PaginationBoxView);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = _PaginationBoxView2.default;
//# sourceMappingURL=index.js.map

/***/ }),

/***/ "./node_modules/react/cjs/react.development.js":
/*!*****************************************************!*\
  !*** ./node_modules/react/cjs/react.development.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/** @license React v16.13.1
 * react.development.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */





if (true) {
  (function() {
'use strict';

var _assign = __webpack_require__(/*! object-assign */ "./node_modules/object-assign/index.js");
var checkPropTypes = __webpack_require__(/*! prop-types/checkPropTypes */ "./node_modules/prop-types/checkPropTypes.js");

var ReactVersion = '16.13.1';

// The Symbol used to tag the ReactElement-like types. If there is no native Symbol
// nor polyfill, then a plain number is used for performance.
var hasSymbol = typeof Symbol === 'function' && Symbol.for;
var REACT_ELEMENT_TYPE = hasSymbol ? Symbol.for('react.element') : 0xeac7;
var REACT_PORTAL_TYPE = hasSymbol ? Symbol.for('react.portal') : 0xeaca;
var REACT_FRAGMENT_TYPE = hasSymbol ? Symbol.for('react.fragment') : 0xeacb;
var REACT_STRICT_MODE_TYPE = hasSymbol ? Symbol.for('react.strict_mode') : 0xeacc;
var REACT_PROFILER_TYPE = hasSymbol ? Symbol.for('react.profiler') : 0xead2;
var REACT_PROVIDER_TYPE = hasSymbol ? Symbol.for('react.provider') : 0xeacd;
var REACT_CONTEXT_TYPE = hasSymbol ? Symbol.for('react.context') : 0xeace; // TODO: We don't use AsyncMode or ConcurrentMode anymore. They were temporary
var REACT_CONCURRENT_MODE_TYPE = hasSymbol ? Symbol.for('react.concurrent_mode') : 0xeacf;
var REACT_FORWARD_REF_TYPE = hasSymbol ? Symbol.for('react.forward_ref') : 0xead0;
var REACT_SUSPENSE_TYPE = hasSymbol ? Symbol.for('react.suspense') : 0xead1;
var REACT_SUSPENSE_LIST_TYPE = hasSymbol ? Symbol.for('react.suspense_list') : 0xead8;
var REACT_MEMO_TYPE = hasSymbol ? Symbol.for('react.memo') : 0xead3;
var REACT_LAZY_TYPE = hasSymbol ? Symbol.for('react.lazy') : 0xead4;
var REACT_BLOCK_TYPE = hasSymbol ? Symbol.for('react.block') : 0xead9;
var REACT_FUNDAMENTAL_TYPE = hasSymbol ? Symbol.for('react.fundamental') : 0xead5;
var REACT_RESPONDER_TYPE = hasSymbol ? Symbol.for('react.responder') : 0xead6;
var REACT_SCOPE_TYPE = hasSymbol ? Symbol.for('react.scope') : 0xead7;
var MAYBE_ITERATOR_SYMBOL = typeof Symbol === 'function' && Symbol.iterator;
var FAUX_ITERATOR_SYMBOL = '@@iterator';
function getIteratorFn(maybeIterable) {
  if (maybeIterable === null || typeof maybeIterable !== 'object') {
    return null;
  }

  var maybeIterator = MAYBE_ITERATOR_SYMBOL && maybeIterable[MAYBE_ITERATOR_SYMBOL] || maybeIterable[FAUX_ITERATOR_SYMBOL];

  if (typeof maybeIterator === 'function') {
    return maybeIterator;
  }

  return null;
}

/**
 * Keeps track of the current dispatcher.
 */
var ReactCurrentDispatcher = {
  /**
   * @internal
   * @type {ReactComponent}
   */
  current: null
};

/**
 * Keeps track of the current batch's configuration such as how long an update
 * should suspend for if it needs to.
 */
var ReactCurrentBatchConfig = {
  suspense: null
};

/**
 * Keeps track of the current owner.
 *
 * The current owner is the component who should own any components that are
 * currently being constructed.
 */
var ReactCurrentOwner = {
  /**
   * @internal
   * @type {ReactComponent}
   */
  current: null
};

var BEFORE_SLASH_RE = /^(.*)[\\\/]/;
function describeComponentFrame (name, source, ownerName) {
  var sourceInfo = '';

  if (source) {
    var path = source.fileName;
    var fileName = path.replace(BEFORE_SLASH_RE, '');

    {
      // In DEV, include code for a common special case:
      // prefer "folder/index.js" instead of just "index.js".
      if (/^index\./.test(fileName)) {
        var match = path.match(BEFORE_SLASH_RE);

        if (match) {
          var pathBeforeSlash = match[1];

          if (pathBeforeSlash) {
            var folderName = pathBeforeSlash.replace(BEFORE_SLASH_RE, '');
            fileName = folderName + '/' + fileName;
          }
        }
      }
    }

    sourceInfo = ' (at ' + fileName + ':' + source.lineNumber + ')';
  } else if (ownerName) {
    sourceInfo = ' (created by ' + ownerName + ')';
  }

  return '\n    in ' + (name || 'Unknown') + sourceInfo;
}

var Resolved = 1;
function refineResolvedLazyComponent(lazyComponent) {
  return lazyComponent._status === Resolved ? lazyComponent._result : null;
}

function getWrappedName(outerType, innerType, wrapperName) {
  var functionName = innerType.displayName || innerType.name || '';
  return outerType.displayName || (functionName !== '' ? wrapperName + "(" + functionName + ")" : wrapperName);
}

function getComponentName(type) {
  if (type == null) {
    // Host root, text node or just invalid type.
    return null;
  }

  {
    if (typeof type.tag === 'number') {
      error('Received an unexpected object in getComponentName(). ' + 'This is likely a bug in React. Please file an issue.');
    }
  }

  if (typeof type === 'function') {
    return type.displayName || type.name || null;
  }

  if (typeof type === 'string') {
    return type;
  }

  switch (type) {
    case REACT_FRAGMENT_TYPE:
      return 'Fragment';

    case REACT_PORTAL_TYPE:
      return 'Portal';

    case REACT_PROFILER_TYPE:
      return "Profiler";

    case REACT_STRICT_MODE_TYPE:
      return 'StrictMode';

    case REACT_SUSPENSE_TYPE:
      return 'Suspense';

    case REACT_SUSPENSE_LIST_TYPE:
      return 'SuspenseList';
  }

  if (typeof type === 'object') {
    switch (type.$$typeof) {
      case REACT_CONTEXT_TYPE:
        return 'Context.Consumer';

      case REACT_PROVIDER_TYPE:
        return 'Context.Provider';

      case REACT_FORWARD_REF_TYPE:
        return getWrappedName(type, type.render, 'ForwardRef');

      case REACT_MEMO_TYPE:
        return getComponentName(type.type);

      case REACT_BLOCK_TYPE:
        return getComponentName(type.render);

      case REACT_LAZY_TYPE:
        {
          var thenable = type;
          var resolvedThenable = refineResolvedLazyComponent(thenable);

          if (resolvedThenable) {
            return getComponentName(resolvedThenable);
          }

          break;
        }
    }
  }

  return null;
}

var ReactDebugCurrentFrame = {};
var currentlyValidatingElement = null;
function setCurrentlyValidatingElement(element) {
  {
    currentlyValidatingElement = element;
  }
}

{
  // Stack implementation injected by the current renderer.
  ReactDebugCurrentFrame.getCurrentStack = null;

  ReactDebugCurrentFrame.getStackAddendum = function () {
    var stack = ''; // Add an extra top frame while an element is being validated

    if (currentlyValidatingElement) {
      var name = getComponentName(currentlyValidatingElement.type);
      var owner = currentlyValidatingElement._owner;
      stack += describeComponentFrame(name, currentlyValidatingElement._source, owner && getComponentName(owner.type));
    } // Delegate to the injected renderer-specific implementation


    var impl = ReactDebugCurrentFrame.getCurrentStack;

    if (impl) {
      stack += impl() || '';
    }

    return stack;
  };
}

/**
 * Used by act() to track whether you're inside an act() scope.
 */
var IsSomeRendererActing = {
  current: false
};

var ReactSharedInternals = {
  ReactCurrentDispatcher: ReactCurrentDispatcher,
  ReactCurrentBatchConfig: ReactCurrentBatchConfig,
  ReactCurrentOwner: ReactCurrentOwner,
  IsSomeRendererActing: IsSomeRendererActing,
  // Used by renderers to avoid bundling object-assign twice in UMD bundles:
  assign: _assign
};

{
  _assign(ReactSharedInternals, {
    // These should not be included in production.
    ReactDebugCurrentFrame: ReactDebugCurrentFrame,
    // Shim for React DOM 16.0.0 which still destructured (but not used) this.
    // TODO: remove in React 17.0.
    ReactComponentTreeHook: {}
  });
}

// by calls to these methods by a Babel plugin.
//
// In PROD (or in packages without access to React internals),
// they are left as they are instead.

function warn(format) {
  {
    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }

    printWarning('warn', format, args);
  }
}
function error(format) {
  {
    for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
      args[_key2 - 1] = arguments[_key2];
    }

    printWarning('error', format, args);
  }
}

function printWarning(level, format, args) {
  // When changing this logic, you might want to also
  // update consoleWithStackDev.www.js as well.
  {
    var hasExistingStack = args.length > 0 && typeof args[args.length - 1] === 'string' && args[args.length - 1].indexOf('\n    in') === 0;

    if (!hasExistingStack) {
      var ReactDebugCurrentFrame = ReactSharedInternals.ReactDebugCurrentFrame;
      var stack = ReactDebugCurrentFrame.getStackAddendum();

      if (stack !== '') {
        format += '%s';
        args = args.concat([stack]);
      }
    }

    var argsWithFormat = args.map(function (item) {
      return '' + item;
    }); // Careful: RN currently depends on this prefix

    argsWithFormat.unshift('Warning: ' + format); // We intentionally don't use spread (or .apply) directly because it
    // breaks IE9: https://github.com/facebook/react/issues/13610
    // eslint-disable-next-line react-internal/no-production-logging

    Function.prototype.apply.call(console[level], console, argsWithFormat);

    try {
      // --- Welcome to debugging React ---
      // This error was thrown as a convenience so that you can use this stack
      // to find the callsite that caused this warning to fire.
      var argIndex = 0;
      var message = 'Warning: ' + format.replace(/%s/g, function () {
        return args[argIndex++];
      });
      throw new Error(message);
    } catch (x) {}
  }
}

var didWarnStateUpdateForUnmountedComponent = {};

function warnNoop(publicInstance, callerName) {
  {
    var _constructor = publicInstance.constructor;
    var componentName = _constructor && (_constructor.displayName || _constructor.name) || 'ReactClass';
    var warningKey = componentName + "." + callerName;

    if (didWarnStateUpdateForUnmountedComponent[warningKey]) {
      return;
    }

    error("Can't call %s on a component that is not yet mounted. " + 'This is a no-op, but it might indicate a bug in your application. ' + 'Instead, assign to `this.state` directly or define a `state = {};` ' + 'class property with the desired state in the %s component.', callerName, componentName);

    didWarnStateUpdateForUnmountedComponent[warningKey] = true;
  }
}
/**
 * This is the abstract API for an update queue.
 */


var ReactNoopUpdateQueue = {
  /**
   * Checks whether or not this composite component is mounted.
   * @param {ReactClass} publicInstance The instance we want to test.
   * @return {boolean} True if mounted, false otherwise.
   * @protected
   * @final
   */
  isMounted: function (publicInstance) {
    return false;
  },

  /**
   * Forces an update. This should only be invoked when it is known with
   * certainty that we are **not** in a DOM transaction.
   *
   * You may want to call this when you know that some deeper aspect of the
   * component's state has changed but `setState` was not called.
   *
   * This will not invoke `shouldComponentUpdate`, but it will invoke
   * `componentWillUpdate` and `componentDidUpdate`.
   *
   * @param {ReactClass} publicInstance The instance that should rerender.
   * @param {?function} callback Called after component is updated.
   * @param {?string} callerName name of the calling function in the public API.
   * @internal
   */
  enqueueForceUpdate: function (publicInstance, callback, callerName) {
    warnNoop(publicInstance, 'forceUpdate');
  },

  /**
   * Replaces all of the state. Always use this or `setState` to mutate state.
   * You should treat `this.state` as immutable.
   *
   * There is no guarantee that `this.state` will be immediately updated, so
   * accessing `this.state` after calling this method may return the old value.
   *
   * @param {ReactClass} publicInstance The instance that should rerender.
   * @param {object} completeState Next state.
   * @param {?function} callback Called after component is updated.
   * @param {?string} callerName name of the calling function in the public API.
   * @internal
   */
  enqueueReplaceState: function (publicInstance, completeState, callback, callerName) {
    warnNoop(publicInstance, 'replaceState');
  },

  /**
   * Sets a subset of the state. This only exists because _pendingState is
   * internal. This provides a merging strategy that is not available to deep
   * properties which is confusing. TODO: Expose pendingState or don't use it
   * during the merge.
   *
   * @param {ReactClass} publicInstance The instance that should rerender.
   * @param {object} partialState Next partial state to be merged with state.
   * @param {?function} callback Called after component is updated.
   * @param {?string} Name of the calling function in the public API.
   * @internal
   */
  enqueueSetState: function (publicInstance, partialState, callback, callerName) {
    warnNoop(publicInstance, 'setState');
  }
};

var emptyObject = {};

{
  Object.freeze(emptyObject);
}
/**
 * Base class helpers for the updating state of a component.
 */


function Component(props, context, updater) {
  this.props = props;
  this.context = context; // If a component has string refs, we will assign a different object later.

  this.refs = emptyObject; // We initialize the default updater but the real one gets injected by the
  // renderer.

  this.updater = updater || ReactNoopUpdateQueue;
}

Component.prototype.isReactComponent = {};
/**
 * Sets a subset of the state. Always use this to mutate
 * state. You should treat `this.state` as immutable.
 *
 * There is no guarantee that `this.state` will be immediately updated, so
 * accessing `this.state` after calling this method may return the old value.
 *
 * There is no guarantee that calls to `setState` will run synchronously,
 * as they may eventually be batched together.  You can provide an optional
 * callback that will be executed when the call to setState is actually
 * completed.
 *
 * When a function is provided to setState, it will be called at some point in
 * the future (not synchronously). It will be called with the up to date
 * component arguments (state, props, context). These values can be different
 * from this.* because your function may be called after receiveProps but before
 * shouldComponentUpdate, and this new state, props, and context will not yet be
 * assigned to this.
 *
 * @param {object|function} partialState Next partial state or function to
 *        produce next partial state to be merged with current state.
 * @param {?function} callback Called after state is updated.
 * @final
 * @protected
 */

Component.prototype.setState = function (partialState, callback) {
  if (!(typeof partialState === 'object' || typeof partialState === 'function' || partialState == null)) {
    {
      throw Error( "setState(...): takes an object of state variables to update or a function which returns an object of state variables." );
    }
  }

  this.updater.enqueueSetState(this, partialState, callback, 'setState');
};
/**
 * Forces an update. This should only be invoked when it is known with
 * certainty that we are **not** in a DOM transaction.
 *
 * You may want to call this when you know that some deeper aspect of the
 * component's state has changed but `setState` was not called.
 *
 * This will not invoke `shouldComponentUpdate`, but it will invoke
 * `componentWillUpdate` and `componentDidUpdate`.
 *
 * @param {?function} callback Called after update is complete.
 * @final
 * @protected
 */


Component.prototype.forceUpdate = function (callback) {
  this.updater.enqueueForceUpdate(this, callback, 'forceUpdate');
};
/**
 * Deprecated APIs. These APIs used to exist on classic React classes but since
 * we would like to deprecate them, we're not going to move them over to this
 * modern base class. Instead, we define a getter that warns if it's accessed.
 */


{
  var deprecatedAPIs = {
    isMounted: ['isMounted', 'Instead, make sure to clean up subscriptions and pending requests in ' + 'componentWillUnmount to prevent memory leaks.'],
    replaceState: ['replaceState', 'Refactor your code to use setState instead (see ' + 'https://github.com/facebook/react/issues/3236).']
  };

  var defineDeprecationWarning = function (methodName, info) {
    Object.defineProperty(Component.prototype, methodName, {
      get: function () {
        warn('%s(...) is deprecated in plain JavaScript React classes. %s', info[0], info[1]);

        return undefined;
      }
    });
  };

  for (var fnName in deprecatedAPIs) {
    if (deprecatedAPIs.hasOwnProperty(fnName)) {
      defineDeprecationWarning(fnName, deprecatedAPIs[fnName]);
    }
  }
}

function ComponentDummy() {}

ComponentDummy.prototype = Component.prototype;
/**
 * Convenience component with default shallow equality check for sCU.
 */

function PureComponent(props, context, updater) {
  this.props = props;
  this.context = context; // If a component has string refs, we will assign a different object later.

  this.refs = emptyObject;
  this.updater = updater || ReactNoopUpdateQueue;
}

var pureComponentPrototype = PureComponent.prototype = new ComponentDummy();
pureComponentPrototype.constructor = PureComponent; // Avoid an extra prototype jump for these methods.

_assign(pureComponentPrototype, Component.prototype);

pureComponentPrototype.isPureReactComponent = true;

// an immutable object with a single mutable value
function createRef() {
  var refObject = {
    current: null
  };

  {
    Object.seal(refObject);
  }

  return refObject;
}

var hasOwnProperty = Object.prototype.hasOwnProperty;
var RESERVED_PROPS = {
  key: true,
  ref: true,
  __self: true,
  __source: true
};
var specialPropKeyWarningShown, specialPropRefWarningShown, didWarnAboutStringRefs;

{
  didWarnAboutStringRefs = {};
}

function hasValidRef(config) {
  {
    if (hasOwnProperty.call(config, 'ref')) {
      var getter = Object.getOwnPropertyDescriptor(config, 'ref').get;

      if (getter && getter.isReactWarning) {
        return false;
      }
    }
  }

  return config.ref !== undefined;
}

function hasValidKey(config) {
  {
    if (hasOwnProperty.call(config, 'key')) {
      var getter = Object.getOwnPropertyDescriptor(config, 'key').get;

      if (getter && getter.isReactWarning) {
        return false;
      }
    }
  }

  return config.key !== undefined;
}

function defineKeyPropWarningGetter(props, displayName) {
  var warnAboutAccessingKey = function () {
    {
      if (!specialPropKeyWarningShown) {
        specialPropKeyWarningShown = true;

        error('%s: `key` is not a prop. Trying to access it will result ' + 'in `undefined` being returned. If you need to access the same ' + 'value within the child component, you should pass it as a different ' + 'prop. (https://fb.me/react-special-props)', displayName);
      }
    }
  };

  warnAboutAccessingKey.isReactWarning = true;
  Object.defineProperty(props, 'key', {
    get: warnAboutAccessingKey,
    configurable: true
  });
}

function defineRefPropWarningGetter(props, displayName) {
  var warnAboutAccessingRef = function () {
    {
      if (!specialPropRefWarningShown) {
        specialPropRefWarningShown = true;

        error('%s: `ref` is not a prop. Trying to access it will result ' + 'in `undefined` being returned. If you need to access the same ' + 'value within the child component, you should pass it as a different ' + 'prop. (https://fb.me/react-special-props)', displayName);
      }
    }
  };

  warnAboutAccessingRef.isReactWarning = true;
  Object.defineProperty(props, 'ref', {
    get: warnAboutAccessingRef,
    configurable: true
  });
}

function warnIfStringRefCannotBeAutoConverted(config) {
  {
    if (typeof config.ref === 'string' && ReactCurrentOwner.current && config.__self && ReactCurrentOwner.current.stateNode !== config.__self) {
      var componentName = getComponentName(ReactCurrentOwner.current.type);

      if (!didWarnAboutStringRefs[componentName]) {
        error('Component "%s" contains the string ref "%s". ' + 'Support for string refs will be removed in a future major release. ' + 'This case cannot be automatically converted to an arrow function. ' + 'We ask you to manually fix this case by using useRef() or createRef() instead. ' + 'Learn more about using refs safely here: ' + 'https://fb.me/react-strict-mode-string-ref', getComponentName(ReactCurrentOwner.current.type), config.ref);

        didWarnAboutStringRefs[componentName] = true;
      }
    }
  }
}
/**
 * Factory method to create a new React element. This no longer adheres to
 * the class pattern, so do not use new to call it. Also, instanceof check
 * will not work. Instead test $$typeof field against Symbol.for('react.element') to check
 * if something is a React Element.
 *
 * @param {*} type
 * @param {*} props
 * @param {*} key
 * @param {string|object} ref
 * @param {*} owner
 * @param {*} self A *temporary* helper to detect places where `this` is
 * different from the `owner` when React.createElement is called, so that we
 * can warn. We want to get rid of owner and replace string `ref`s with arrow
 * functions, and as long as `this` and owner are the same, there will be no
 * change in behavior.
 * @param {*} source An annotation object (added by a transpiler or otherwise)
 * indicating filename, line number, and/or other information.
 * @internal
 */


var ReactElement = function (type, key, ref, self, source, owner, props) {
  var element = {
    // This tag allows us to uniquely identify this as a React Element
    $$typeof: REACT_ELEMENT_TYPE,
    // Built-in properties that belong on the element
    type: type,
    key: key,
    ref: ref,
    props: props,
    // Record the component responsible for creating this element.
    _owner: owner
  };

  {
    // The validation flag is currently mutative. We put it on
    // an external backing store so that we can freeze the whole object.
    // This can be replaced with a WeakMap once they are implemented in
    // commonly used development environments.
    element._store = {}; // To make comparing ReactElements easier for testing purposes, we make
    // the validation flag non-enumerable (where possible, which should
    // include every environment we run tests in), so the test framework
    // ignores it.

    Object.defineProperty(element._store, 'validated', {
      configurable: false,
      enumerable: false,
      writable: true,
      value: false
    }); // self and source are DEV only properties.

    Object.defineProperty(element, '_self', {
      configurable: false,
      enumerable: false,
      writable: false,
      value: self
    }); // Two elements created in two different places should be considered
    // equal for testing purposes and therefore we hide it from enumeration.

    Object.defineProperty(element, '_source', {
      configurable: false,
      enumerable: false,
      writable: false,
      value: source
    });

    if (Object.freeze) {
      Object.freeze(element.props);
      Object.freeze(element);
    }
  }

  return element;
};
/**
 * Create and return a new ReactElement of the given type.
 * See https://reactjs.org/docs/react-api.html#createelement
 */

function createElement(type, config, children) {
  var propName; // Reserved names are extracted

  var props = {};
  var key = null;
  var ref = null;
  var self = null;
  var source = null;

  if (config != null) {
    if (hasValidRef(config)) {
      ref = config.ref;

      {
        warnIfStringRefCannotBeAutoConverted(config);
      }
    }

    if (hasValidKey(config)) {
      key = '' + config.key;
    }

    self = config.__self === undefined ? null : config.__self;
    source = config.__source === undefined ? null : config.__source; // Remaining properties are added to a new props object

    for (propName in config) {
      if (hasOwnProperty.call(config, propName) && !RESERVED_PROPS.hasOwnProperty(propName)) {
        props[propName] = config[propName];
      }
    }
  } // Children can be more than one argument, and those are transferred onto
  // the newly allocated props object.


  var childrenLength = arguments.length - 2;

  if (childrenLength === 1) {
    props.children = children;
  } else if (childrenLength > 1) {
    var childArray = Array(childrenLength);

    for (var i = 0; i < childrenLength; i++) {
      childArray[i] = arguments[i + 2];
    }

    {
      if (Object.freeze) {
        Object.freeze(childArray);
      }
    }

    props.children = childArray;
  } // Resolve default props


  if (type && type.defaultProps) {
    var defaultProps = type.defaultProps;

    for (propName in defaultProps) {
      if (props[propName] === undefined) {
        props[propName] = defaultProps[propName];
      }
    }
  }

  {
    if (key || ref) {
      var displayName = typeof type === 'function' ? type.displayName || type.name || 'Unknown' : type;

      if (key) {
        defineKeyPropWarningGetter(props, displayName);
      }

      if (ref) {
        defineRefPropWarningGetter(props, displayName);
      }
    }
  }

  return ReactElement(type, key, ref, self, source, ReactCurrentOwner.current, props);
}
function cloneAndReplaceKey(oldElement, newKey) {
  var newElement = ReactElement(oldElement.type, newKey, oldElement.ref, oldElement._self, oldElement._source, oldElement._owner, oldElement.props);
  return newElement;
}
/**
 * Clone and return a new ReactElement using element as the starting point.
 * See https://reactjs.org/docs/react-api.html#cloneelement
 */

function cloneElement(element, config, children) {
  if (!!(element === null || element === undefined)) {
    {
      throw Error( "React.cloneElement(...): The argument must be a React element, but you passed " + element + "." );
    }
  }

  var propName; // Original props are copied

  var props = _assign({}, element.props); // Reserved names are extracted


  var key = element.key;
  var ref = element.ref; // Self is preserved since the owner is preserved.

  var self = element._self; // Source is preserved since cloneElement is unlikely to be targeted by a
  // transpiler, and the original source is probably a better indicator of the
  // true owner.

  var source = element._source; // Owner will be preserved, unless ref is overridden

  var owner = element._owner;

  if (config != null) {
    if (hasValidRef(config)) {
      // Silently steal the ref from the parent.
      ref = config.ref;
      owner = ReactCurrentOwner.current;
    }

    if (hasValidKey(config)) {
      key = '' + config.key;
    } // Remaining properties override existing props


    var defaultProps;

    if (element.type && element.type.defaultProps) {
      defaultProps = element.type.defaultProps;
    }

    for (propName in config) {
      if (hasOwnProperty.call(config, propName) && !RESERVED_PROPS.hasOwnProperty(propName)) {
        if (config[propName] === undefined && defaultProps !== undefined) {
          // Resolve default props
          props[propName] = defaultProps[propName];
        } else {
          props[propName] = config[propName];
        }
      }
    }
  } // Children can be more than one argument, and those are transferred onto
  // the newly allocated props object.


  var childrenLength = arguments.length - 2;

  if (childrenLength === 1) {
    props.children = children;
  } else if (childrenLength > 1) {
    var childArray = Array(childrenLength);

    for (var i = 0; i < childrenLength; i++) {
      childArray[i] = arguments[i + 2];
    }

    props.children = childArray;
  }

  return ReactElement(element.type, key, ref, self, source, owner, props);
}
/**
 * Verifies the object is a ReactElement.
 * See https://reactjs.org/docs/react-api.html#isvalidelement
 * @param {?object} object
 * @return {boolean} True if `object` is a ReactElement.
 * @final
 */

function isValidElement(object) {
  return typeof object === 'object' && object !== null && object.$$typeof === REACT_ELEMENT_TYPE;
}

var SEPARATOR = '.';
var SUBSEPARATOR = ':';
/**
 * Escape and wrap key so it is safe to use as a reactid
 *
 * @param {string} key to be escaped.
 * @return {string} the escaped key.
 */

function escape(key) {
  var escapeRegex = /[=:]/g;
  var escaperLookup = {
    '=': '=0',
    ':': '=2'
  };
  var escapedString = ('' + key).replace(escapeRegex, function (match) {
    return escaperLookup[match];
  });
  return '$' + escapedString;
}
/**
 * TODO: Test that a single child and an array with one item have the same key
 * pattern.
 */


var didWarnAboutMaps = false;
var userProvidedKeyEscapeRegex = /\/+/g;

function escapeUserProvidedKey(text) {
  return ('' + text).replace(userProvidedKeyEscapeRegex, '$&/');
}

var POOL_SIZE = 10;
var traverseContextPool = [];

function getPooledTraverseContext(mapResult, keyPrefix, mapFunction, mapContext) {
  if (traverseContextPool.length) {
    var traverseContext = traverseContextPool.pop();
    traverseContext.result = mapResult;
    traverseContext.keyPrefix = keyPrefix;
    traverseContext.func = mapFunction;
    traverseContext.context = mapContext;
    traverseContext.count = 0;
    return traverseContext;
  } else {
    return {
      result: mapResult,
      keyPrefix: keyPrefix,
      func: mapFunction,
      context: mapContext,
      count: 0
    };
  }
}

function releaseTraverseContext(traverseContext) {
  traverseContext.result = null;
  traverseContext.keyPrefix = null;
  traverseContext.func = null;
  traverseContext.context = null;
  traverseContext.count = 0;

  if (traverseContextPool.length < POOL_SIZE) {
    traverseContextPool.push(traverseContext);
  }
}
/**
 * @param {?*} children Children tree container.
 * @param {!string} nameSoFar Name of the key path so far.
 * @param {!function} callback Callback to invoke with each child found.
 * @param {?*} traverseContext Used to pass information throughout the traversal
 * process.
 * @return {!number} The number of children in this subtree.
 */


function traverseAllChildrenImpl(children, nameSoFar, callback, traverseContext) {
  var type = typeof children;

  if (type === 'undefined' || type === 'boolean') {
    // All of the above are perceived as null.
    children = null;
  }

  var invokeCallback = false;

  if (children === null) {
    invokeCallback = true;
  } else {
    switch (type) {
      case 'string':
      case 'number':
        invokeCallback = true;
        break;

      case 'object':
        switch (children.$$typeof) {
          case REACT_ELEMENT_TYPE:
          case REACT_PORTAL_TYPE:
            invokeCallback = true;
        }

    }
  }

  if (invokeCallback) {
    callback(traverseContext, children, // If it's the only child, treat the name as if it was wrapped in an array
    // so that it's consistent if the number of children grows.
    nameSoFar === '' ? SEPARATOR + getComponentKey(children, 0) : nameSoFar);
    return 1;
  }

  var child;
  var nextName;
  var subtreeCount = 0; // Count of children found in the current subtree.

  var nextNamePrefix = nameSoFar === '' ? SEPARATOR : nameSoFar + SUBSEPARATOR;

  if (Array.isArray(children)) {
    for (var i = 0; i < children.length; i++) {
      child = children[i];
      nextName = nextNamePrefix + getComponentKey(child, i);
      subtreeCount += traverseAllChildrenImpl(child, nextName, callback, traverseContext);
    }
  } else {
    var iteratorFn = getIteratorFn(children);

    if (typeof iteratorFn === 'function') {

      {
        // Warn about using Maps as children
        if (iteratorFn === children.entries) {
          if (!didWarnAboutMaps) {
            warn('Using Maps as children is deprecated and will be removed in ' + 'a future major release. Consider converting children to ' + 'an array of keyed ReactElements instead.');
          }

          didWarnAboutMaps = true;
        }
      }

      var iterator = iteratorFn.call(children);
      var step;
      var ii = 0;

      while (!(step = iterator.next()).done) {
        child = step.value;
        nextName = nextNamePrefix + getComponentKey(child, ii++);
        subtreeCount += traverseAllChildrenImpl(child, nextName, callback, traverseContext);
      }
    } else if (type === 'object') {
      var addendum = '';

      {
        addendum = ' If you meant to render a collection of children, use an array ' + 'instead.' + ReactDebugCurrentFrame.getStackAddendum();
      }

      var childrenString = '' + children;

      {
        {
          throw Error( "Objects are not valid as a React child (found: " + (childrenString === '[object Object]' ? 'object with keys {' + Object.keys(children).join(', ') + '}' : childrenString) + ")." + addendum );
        }
      }
    }
  }

  return subtreeCount;
}
/**
 * Traverses children that are typically specified as `props.children`, but
 * might also be specified through attributes:
 *
 * - `traverseAllChildren(this.props.children, ...)`
 * - `traverseAllChildren(this.props.leftPanelChildren, ...)`
 *
 * The `traverseContext` is an optional argument that is passed through the
 * entire traversal. It can be used to store accumulations or anything else that
 * the callback might find relevant.
 *
 * @param {?*} children Children tree object.
 * @param {!function} callback To invoke upon traversing each child.
 * @param {?*} traverseContext Context for traversal.
 * @return {!number} The number of children in this subtree.
 */


function traverseAllChildren(children, callback, traverseContext) {
  if (children == null) {
    return 0;
  }

  return traverseAllChildrenImpl(children, '', callback, traverseContext);
}
/**
 * Generate a key string that identifies a component within a set.
 *
 * @param {*} component A component that could contain a manual key.
 * @param {number} index Index that is used if a manual key is not provided.
 * @return {string}
 */


function getComponentKey(component, index) {
  // Do some typechecking here since we call this blindly. We want to ensure
  // that we don't block potential future ES APIs.
  if (typeof component === 'object' && component !== null && component.key != null) {
    // Explicit key
    return escape(component.key);
  } // Implicit key determined by the index in the set


  return index.toString(36);
}

function forEachSingleChild(bookKeeping, child, name) {
  var func = bookKeeping.func,
      context = bookKeeping.context;
  func.call(context, child, bookKeeping.count++);
}
/**
 * Iterates through children that are typically specified as `props.children`.
 *
 * See https://reactjs.org/docs/react-api.html#reactchildrenforeach
 *
 * The provided forEachFunc(child, index) will be called for each
 * leaf child.
 *
 * @param {?*} children Children tree container.
 * @param {function(*, int)} forEachFunc
 * @param {*} forEachContext Context for forEachContext.
 */


function forEachChildren(children, forEachFunc, forEachContext) {
  if (children == null) {
    return children;
  }

  var traverseContext = getPooledTraverseContext(null, null, forEachFunc, forEachContext);
  traverseAllChildren(children, forEachSingleChild, traverseContext);
  releaseTraverseContext(traverseContext);
}

function mapSingleChildIntoContext(bookKeeping, child, childKey) {
  var result = bookKeeping.result,
      keyPrefix = bookKeeping.keyPrefix,
      func = bookKeeping.func,
      context = bookKeeping.context;
  var mappedChild = func.call(context, child, bookKeeping.count++);

  if (Array.isArray(mappedChild)) {
    mapIntoWithKeyPrefixInternal(mappedChild, result, childKey, function (c) {
      return c;
    });
  } else if (mappedChild != null) {
    if (isValidElement(mappedChild)) {
      mappedChild = cloneAndReplaceKey(mappedChild, // Keep both the (mapped) and old keys if they differ, just as
      // traverseAllChildren used to do for objects as children
      keyPrefix + (mappedChild.key && (!child || child.key !== mappedChild.key) ? escapeUserProvidedKey(mappedChild.key) + '/' : '') + childKey);
    }

    result.push(mappedChild);
  }
}

function mapIntoWithKeyPrefixInternal(children, array, prefix, func, context) {
  var escapedPrefix = '';

  if (prefix != null) {
    escapedPrefix = escapeUserProvidedKey(prefix) + '/';
  }

  var traverseContext = getPooledTraverseContext(array, escapedPrefix, func, context);
  traverseAllChildren(children, mapSingleChildIntoContext, traverseContext);
  releaseTraverseContext(traverseContext);
}
/**
 * Maps children that are typically specified as `props.children`.
 *
 * See https://reactjs.org/docs/react-api.html#reactchildrenmap
 *
 * The provided mapFunction(child, key, index) will be called for each
 * leaf child.
 *
 * @param {?*} children Children tree container.
 * @param {function(*, int)} func The map function.
 * @param {*} context Context for mapFunction.
 * @return {object} Object containing the ordered map of results.
 */


function mapChildren(children, func, context) {
  if (children == null) {
    return children;
  }

  var result = [];
  mapIntoWithKeyPrefixInternal(children, result, null, func, context);
  return result;
}
/**
 * Count the number of children that are typically specified as
 * `props.children`.
 *
 * See https://reactjs.org/docs/react-api.html#reactchildrencount
 *
 * @param {?*} children Children tree container.
 * @return {number} The number of children.
 */


function countChildren(children) {
  return traverseAllChildren(children, function () {
    return null;
  }, null);
}
/**
 * Flatten a children object (typically specified as `props.children`) and
 * return an array with appropriately re-keyed children.
 *
 * See https://reactjs.org/docs/react-api.html#reactchildrentoarray
 */


function toArray(children) {
  var result = [];
  mapIntoWithKeyPrefixInternal(children, result, null, function (child) {
    return child;
  });
  return result;
}
/**
 * Returns the first child in a collection of children and verifies that there
 * is only one child in the collection.
 *
 * See https://reactjs.org/docs/react-api.html#reactchildrenonly
 *
 * The current implementation of this function assumes that a single child gets
 * passed without a wrapper, but the purpose of this helper function is to
 * abstract away the particular structure of children.
 *
 * @param {?object} children Child collection structure.
 * @return {ReactElement} The first and only `ReactElement` contained in the
 * structure.
 */


function onlyChild(children) {
  if (!isValidElement(children)) {
    {
      throw Error( "React.Children.only expected to receive a single React element child." );
    }
  }

  return children;
}

function createContext(defaultValue, calculateChangedBits) {
  if (calculateChangedBits === undefined) {
    calculateChangedBits = null;
  } else {
    {
      if (calculateChangedBits !== null && typeof calculateChangedBits !== 'function') {
        error('createContext: Expected the optional second argument to be a ' + 'function. Instead received: %s', calculateChangedBits);
      }
    }
  }

  var context = {
    $$typeof: REACT_CONTEXT_TYPE,
    _calculateChangedBits: calculateChangedBits,
    // As a workaround to support multiple concurrent renderers, we categorize
    // some renderers as primary and others as secondary. We only expect
    // there to be two concurrent renderers at most: React Native (primary) and
    // Fabric (secondary); React DOM (primary) and React ART (secondary).
    // Secondary renderers store their context values on separate fields.
    _currentValue: defaultValue,
    _currentValue2: defaultValue,
    // Used to track how many concurrent renderers this context currently
    // supports within in a single renderer. Such as parallel server rendering.
    _threadCount: 0,
    // These are circular
    Provider: null,
    Consumer: null
  };
  context.Provider = {
    $$typeof: REACT_PROVIDER_TYPE,
    _context: context
  };
  var hasWarnedAboutUsingNestedContextConsumers = false;
  var hasWarnedAboutUsingConsumerProvider = false;

  {
    // A separate object, but proxies back to the original context object for
    // backwards compatibility. It has a different $$typeof, so we can properly
    // warn for the incorrect usage of Context as a Consumer.
    var Consumer = {
      $$typeof: REACT_CONTEXT_TYPE,
      _context: context,
      _calculateChangedBits: context._calculateChangedBits
    }; // $FlowFixMe: Flow complains about not setting a value, which is intentional here

    Object.defineProperties(Consumer, {
      Provider: {
        get: function () {
          if (!hasWarnedAboutUsingConsumerProvider) {
            hasWarnedAboutUsingConsumerProvider = true;

            error('Rendering <Context.Consumer.Provider> is not supported and will be removed in ' + 'a future major release. Did you mean to render <Context.Provider> instead?');
          }

          return context.Provider;
        },
        set: function (_Provider) {
          context.Provider = _Provider;
        }
      },
      _currentValue: {
        get: function () {
          return context._currentValue;
        },
        set: function (_currentValue) {
          context._currentValue = _currentValue;
        }
      },
      _currentValue2: {
        get: function () {
          return context._currentValue2;
        },
        set: function (_currentValue2) {
          context._currentValue2 = _currentValue2;
        }
      },
      _threadCount: {
        get: function () {
          return context._threadCount;
        },
        set: function (_threadCount) {
          context._threadCount = _threadCount;
        }
      },
      Consumer: {
        get: function () {
          if (!hasWarnedAboutUsingNestedContextConsumers) {
            hasWarnedAboutUsingNestedContextConsumers = true;

            error('Rendering <Context.Consumer.Consumer> is not supported and will be removed in ' + 'a future major release. Did you mean to render <Context.Consumer> instead?');
          }

          return context.Consumer;
        }
      }
    }); // $FlowFixMe: Flow complains about missing properties because it doesn't understand defineProperty

    context.Consumer = Consumer;
  }

  {
    context._currentRenderer = null;
    context._currentRenderer2 = null;
  }

  return context;
}

function lazy(ctor) {
  var lazyType = {
    $$typeof: REACT_LAZY_TYPE,
    _ctor: ctor,
    // React uses these fields to store the result.
    _status: -1,
    _result: null
  };

  {
    // In production, this would just set it on the object.
    var defaultProps;
    var propTypes;
    Object.defineProperties(lazyType, {
      defaultProps: {
        configurable: true,
        get: function () {
          return defaultProps;
        },
        set: function (newDefaultProps) {
          error('React.lazy(...): It is not supported to assign `defaultProps` to ' + 'a lazy component import. Either specify them where the component ' + 'is defined, or create a wrapping component around it.');

          defaultProps = newDefaultProps; // Match production behavior more closely:

          Object.defineProperty(lazyType, 'defaultProps', {
            enumerable: true
          });
        }
      },
      propTypes: {
        configurable: true,
        get: function () {
          return propTypes;
        },
        set: function (newPropTypes) {
          error('React.lazy(...): It is not supported to assign `propTypes` to ' + 'a lazy component import. Either specify them where the component ' + 'is defined, or create a wrapping component around it.');

          propTypes = newPropTypes; // Match production behavior more closely:

          Object.defineProperty(lazyType, 'propTypes', {
            enumerable: true
          });
        }
      }
    });
  }

  return lazyType;
}

function forwardRef(render) {
  {
    if (render != null && render.$$typeof === REACT_MEMO_TYPE) {
      error('forwardRef requires a render function but received a `memo` ' + 'component. Instead of forwardRef(memo(...)), use ' + 'memo(forwardRef(...)).');
    } else if (typeof render !== 'function') {
      error('forwardRef requires a render function but was given %s.', render === null ? 'null' : typeof render);
    } else {
      if (render.length !== 0 && render.length !== 2) {
        error('forwardRef render functions accept exactly two parameters: props and ref. %s', render.length === 1 ? 'Did you forget to use the ref parameter?' : 'Any additional parameter will be undefined.');
      }
    }

    if (render != null) {
      if (render.defaultProps != null || render.propTypes != null) {
        error('forwardRef render functions do not support propTypes or defaultProps. ' + 'Did you accidentally pass a React component?');
      }
    }
  }

  return {
    $$typeof: REACT_FORWARD_REF_TYPE,
    render: render
  };
}

function isValidElementType(type) {
  return typeof type === 'string' || typeof type === 'function' || // Note: its typeof might be other than 'symbol' or 'number' if it's a polyfill.
  type === REACT_FRAGMENT_TYPE || type === REACT_CONCURRENT_MODE_TYPE || type === REACT_PROFILER_TYPE || type === REACT_STRICT_MODE_TYPE || type === REACT_SUSPENSE_TYPE || type === REACT_SUSPENSE_LIST_TYPE || typeof type === 'object' && type !== null && (type.$$typeof === REACT_LAZY_TYPE || type.$$typeof === REACT_MEMO_TYPE || type.$$typeof === REACT_PROVIDER_TYPE || type.$$typeof === REACT_CONTEXT_TYPE || type.$$typeof === REACT_FORWARD_REF_TYPE || type.$$typeof === REACT_FUNDAMENTAL_TYPE || type.$$typeof === REACT_RESPONDER_TYPE || type.$$typeof === REACT_SCOPE_TYPE || type.$$typeof === REACT_BLOCK_TYPE);
}

function memo(type, compare) {
  {
    if (!isValidElementType(type)) {
      error('memo: The first argument must be a component. Instead ' + 'received: %s', type === null ? 'null' : typeof type);
    }
  }

  return {
    $$typeof: REACT_MEMO_TYPE,
    type: type,
    compare: compare === undefined ? null : compare
  };
}

function resolveDispatcher() {
  var dispatcher = ReactCurrentDispatcher.current;

  if (!(dispatcher !== null)) {
    {
      throw Error( "Invalid hook call. Hooks can only be called inside of the body of a function component. This could happen for one of the following reasons:\n1. You might have mismatching versions of React and the renderer (such as React DOM)\n2. You might be breaking the Rules of Hooks\n3. You might have more than one copy of React in the same app\nSee https://fb.me/react-invalid-hook-call for tips about how to debug and fix this problem." );
    }
  }

  return dispatcher;
}

function useContext(Context, unstable_observedBits) {
  var dispatcher = resolveDispatcher();

  {
    if (unstable_observedBits !== undefined) {
      error('useContext() second argument is reserved for future ' + 'use in React. Passing it is not supported. ' + 'You passed: %s.%s', unstable_observedBits, typeof unstable_observedBits === 'number' && Array.isArray(arguments[2]) ? '\n\nDid you call array.map(useContext)? ' + 'Calling Hooks inside a loop is not supported. ' + 'Learn more at https://fb.me/rules-of-hooks' : '');
    } // TODO: add a more generic warning for invalid values.


    if (Context._context !== undefined) {
      var realContext = Context._context; // Don't deduplicate because this legitimately causes bugs
      // and nobody should be using this in existing code.

      if (realContext.Consumer === Context) {
        error('Calling useContext(Context.Consumer) is not supported, may cause bugs, and will be ' + 'removed in a future major release. Did you mean to call useContext(Context) instead?');
      } else if (realContext.Provider === Context) {
        error('Calling useContext(Context.Provider) is not supported. ' + 'Did you mean to call useContext(Context) instead?');
      }
    }
  }

  return dispatcher.useContext(Context, unstable_observedBits);
}
function useState(initialState) {
  var dispatcher = resolveDispatcher();
  return dispatcher.useState(initialState);
}
function useReducer(reducer, initialArg, init) {
  var dispatcher = resolveDispatcher();
  return dispatcher.useReducer(reducer, initialArg, init);
}
function useRef(initialValue) {
  var dispatcher = resolveDispatcher();
  return dispatcher.useRef(initialValue);
}
function useEffect(create, deps) {
  var dispatcher = resolveDispatcher();
  return dispatcher.useEffect(create, deps);
}
function useLayoutEffect(create, deps) {
  var dispatcher = resolveDispatcher();
  return dispatcher.useLayoutEffect(create, deps);
}
function useCallback(callback, deps) {
  var dispatcher = resolveDispatcher();
  return dispatcher.useCallback(callback, deps);
}
function useMemo(create, deps) {
  var dispatcher = resolveDispatcher();
  return dispatcher.useMemo(create, deps);
}
function useImperativeHandle(ref, create, deps) {
  var dispatcher = resolveDispatcher();
  return dispatcher.useImperativeHandle(ref, create, deps);
}
function useDebugValue(value, formatterFn) {
  {
    var dispatcher = resolveDispatcher();
    return dispatcher.useDebugValue(value, formatterFn);
  }
}

var propTypesMisspellWarningShown;

{
  propTypesMisspellWarningShown = false;
}

function getDeclarationErrorAddendum() {
  if (ReactCurrentOwner.current) {
    var name = getComponentName(ReactCurrentOwner.current.type);

    if (name) {
      return '\n\nCheck the render method of `' + name + '`.';
    }
  }

  return '';
}

function getSourceInfoErrorAddendum(source) {
  if (source !== undefined) {
    var fileName = source.fileName.replace(/^.*[\\\/]/, '');
    var lineNumber = source.lineNumber;
    return '\n\nCheck your code at ' + fileName + ':' + lineNumber + '.';
  }

  return '';
}

function getSourceInfoErrorAddendumForProps(elementProps) {
  if (elementProps !== null && elementProps !== undefined) {
    return getSourceInfoErrorAddendum(elementProps.__source);
  }

  return '';
}
/**
 * Warn if there's no key explicitly set on dynamic arrays of children or
 * object keys are not valid. This allows us to keep track of children between
 * updates.
 */


var ownerHasKeyUseWarning = {};

function getCurrentComponentErrorInfo(parentType) {
  var info = getDeclarationErrorAddendum();

  if (!info) {
    var parentName = typeof parentType === 'string' ? parentType : parentType.displayName || parentType.name;

    if (parentName) {
      info = "\n\nCheck the top-level render call using <" + parentName + ">.";
    }
  }

  return info;
}
/**
 * Warn if the element doesn't have an explicit key assigned to it.
 * This element is in an array. The array could grow and shrink or be
 * reordered. All children that haven't already been validated are required to
 * have a "key" property assigned to it. Error statuses are cached so a warning
 * will only be shown once.
 *
 * @internal
 * @param {ReactElement} element Element that requires a key.
 * @param {*} parentType element's parent's type.
 */


function validateExplicitKey(element, parentType) {
  if (!element._store || element._store.validated || element.key != null) {
    return;
  }

  element._store.validated = true;
  var currentComponentErrorInfo = getCurrentComponentErrorInfo(parentType);

  if (ownerHasKeyUseWarning[currentComponentErrorInfo]) {
    return;
  }

  ownerHasKeyUseWarning[currentComponentErrorInfo] = true; // Usually the current owner is the offender, but if it accepts children as a
  // property, it may be the creator of the child that's responsible for
  // assigning it a key.

  var childOwner = '';

  if (element && element._owner && element._owner !== ReactCurrentOwner.current) {
    // Give the component that originally created this child.
    childOwner = " It was passed a child from " + getComponentName(element._owner.type) + ".";
  }

  setCurrentlyValidatingElement(element);

  {
    error('Each child in a list should have a unique "key" prop.' + '%s%s See https://fb.me/react-warning-keys for more information.', currentComponentErrorInfo, childOwner);
  }

  setCurrentlyValidatingElement(null);
}
/**
 * Ensure that every element either is passed in a static location, in an
 * array with an explicit keys property defined, or in an object literal
 * with valid key property.
 *
 * @internal
 * @param {ReactNode} node Statically passed child of any type.
 * @param {*} parentType node's parent's type.
 */


function validateChildKeys(node, parentType) {
  if (typeof node !== 'object') {
    return;
  }

  if (Array.isArray(node)) {
    for (var i = 0; i < node.length; i++) {
      var child = node[i];

      if (isValidElement(child)) {
        validateExplicitKey(child, parentType);
      }
    }
  } else if (isValidElement(node)) {
    // This element was passed in a valid location.
    if (node._store) {
      node._store.validated = true;
    }
  } else if (node) {
    var iteratorFn = getIteratorFn(node);

    if (typeof iteratorFn === 'function') {
      // Entry iterators used to provide implicit keys,
      // but now we print a separate warning for them later.
      if (iteratorFn !== node.entries) {
        var iterator = iteratorFn.call(node);
        var step;

        while (!(step = iterator.next()).done) {
          if (isValidElement(step.value)) {
            validateExplicitKey(step.value, parentType);
          }
        }
      }
    }
  }
}
/**
 * Given an element, validate that its props follow the propTypes definition,
 * provided by the type.
 *
 * @param {ReactElement} element
 */


function validatePropTypes(element) {
  {
    var type = element.type;

    if (type === null || type === undefined || typeof type === 'string') {
      return;
    }

    var name = getComponentName(type);
    var propTypes;

    if (typeof type === 'function') {
      propTypes = type.propTypes;
    } else if (typeof type === 'object' && (type.$$typeof === REACT_FORWARD_REF_TYPE || // Note: Memo only checks outer props here.
    // Inner props are checked in the reconciler.
    type.$$typeof === REACT_MEMO_TYPE)) {
      propTypes = type.propTypes;
    } else {
      return;
    }

    if (propTypes) {
      setCurrentlyValidatingElement(element);
      checkPropTypes(propTypes, element.props, 'prop', name, ReactDebugCurrentFrame.getStackAddendum);
      setCurrentlyValidatingElement(null);
    } else if (type.PropTypes !== undefined && !propTypesMisspellWarningShown) {
      propTypesMisspellWarningShown = true;

      error('Component %s declared `PropTypes` instead of `propTypes`. Did you misspell the property assignment?', name || 'Unknown');
    }

    if (typeof type.getDefaultProps === 'function' && !type.getDefaultProps.isReactClassApproved) {
      error('getDefaultProps is only used on classic React.createClass ' + 'definitions. Use a static property named `defaultProps` instead.');
    }
  }
}
/**
 * Given a fragment, validate that it can only be provided with fragment props
 * @param {ReactElement} fragment
 */


function validateFragmentProps(fragment) {
  {
    setCurrentlyValidatingElement(fragment);
    var keys = Object.keys(fragment.props);

    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];

      if (key !== 'children' && key !== 'key') {
        error('Invalid prop `%s` supplied to `React.Fragment`. ' + 'React.Fragment can only have `key` and `children` props.', key);

        break;
      }
    }

    if (fragment.ref !== null) {
      error('Invalid attribute `ref` supplied to `React.Fragment`.');
    }

    setCurrentlyValidatingElement(null);
  }
}
function createElementWithValidation(type, props, children) {
  var validType = isValidElementType(type); // We warn in this case but don't throw. We expect the element creation to
  // succeed and there will likely be errors in render.

  if (!validType) {
    var info = '';

    if (type === undefined || typeof type === 'object' && type !== null && Object.keys(type).length === 0) {
      info += ' You likely forgot to export your component from the file ' + "it's defined in, or you might have mixed up default and named imports.";
    }

    var sourceInfo = getSourceInfoErrorAddendumForProps(props);

    if (sourceInfo) {
      info += sourceInfo;
    } else {
      info += getDeclarationErrorAddendum();
    }

    var typeString;

    if (type === null) {
      typeString = 'null';
    } else if (Array.isArray(type)) {
      typeString = 'array';
    } else if (type !== undefined && type.$$typeof === REACT_ELEMENT_TYPE) {
      typeString = "<" + (getComponentName(type.type) || 'Unknown') + " />";
      info = ' Did you accidentally export a JSX literal instead of a component?';
    } else {
      typeString = typeof type;
    }

    {
      error('React.createElement: type is invalid -- expected a string (for ' + 'built-in components) or a class/function (for composite ' + 'components) but got: %s.%s', typeString, info);
    }
  }

  var element = createElement.apply(this, arguments); // The result can be nullish if a mock or a custom function is used.
  // TODO: Drop this when these are no longer allowed as the type argument.

  if (element == null) {
    return element;
  } // Skip key warning if the type isn't valid since our key validation logic
  // doesn't expect a non-string/function type and can throw confusing errors.
  // We don't want exception behavior to differ between dev and prod.
  // (Rendering will throw with a helpful message and as soon as the type is
  // fixed, the key warnings will appear.)


  if (validType) {
    for (var i = 2; i < arguments.length; i++) {
      validateChildKeys(arguments[i], type);
    }
  }

  if (type === REACT_FRAGMENT_TYPE) {
    validateFragmentProps(element);
  } else {
    validatePropTypes(element);
  }

  return element;
}
var didWarnAboutDeprecatedCreateFactory = false;
function createFactoryWithValidation(type) {
  var validatedFactory = createElementWithValidation.bind(null, type);
  validatedFactory.type = type;

  {
    if (!didWarnAboutDeprecatedCreateFactory) {
      didWarnAboutDeprecatedCreateFactory = true;

      warn('React.createFactory() is deprecated and will be removed in ' + 'a future major release. Consider using JSX ' + 'or use React.createElement() directly instead.');
    } // Legacy hook: remove it


    Object.defineProperty(validatedFactory, 'type', {
      enumerable: false,
      get: function () {
        warn('Factory.type is deprecated. Access the class directly ' + 'before passing it to createFactory.');

        Object.defineProperty(this, 'type', {
          value: type
        });
        return type;
      }
    });
  }

  return validatedFactory;
}
function cloneElementWithValidation(element, props, children) {
  var newElement = cloneElement.apply(this, arguments);

  for (var i = 2; i < arguments.length; i++) {
    validateChildKeys(arguments[i], newElement.type);
  }

  validatePropTypes(newElement);
  return newElement;
}

{

  try {
    var frozenObject = Object.freeze({});
    var testMap = new Map([[frozenObject, null]]);
    var testSet = new Set([frozenObject]); // This is necessary for Rollup to not consider these unused.
    // https://github.com/rollup/rollup/issues/1771
    // TODO: we can remove these if Rollup fixes the bug.

    testMap.set(0, 0);
    testSet.add(0);
  } catch (e) {
  }
}

var createElement$1 =  createElementWithValidation ;
var cloneElement$1 =  cloneElementWithValidation ;
var createFactory =  createFactoryWithValidation ;
var Children = {
  map: mapChildren,
  forEach: forEachChildren,
  count: countChildren,
  toArray: toArray,
  only: onlyChild
};

exports.Children = Children;
exports.Component = Component;
exports.Fragment = REACT_FRAGMENT_TYPE;
exports.Profiler = REACT_PROFILER_TYPE;
exports.PureComponent = PureComponent;
exports.StrictMode = REACT_STRICT_MODE_TYPE;
exports.Suspense = REACT_SUSPENSE_TYPE;
exports.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED = ReactSharedInternals;
exports.cloneElement = cloneElement$1;
exports.createContext = createContext;
exports.createElement = createElement$1;
exports.createFactory = createFactory;
exports.createRef = createRef;
exports.forwardRef = forwardRef;
exports.isValidElement = isValidElement;
exports.lazy = lazy;
exports.memo = memo;
exports.useCallback = useCallback;
exports.useContext = useContext;
exports.useDebugValue = useDebugValue;
exports.useEffect = useEffect;
exports.useImperativeHandle = useImperativeHandle;
exports.useLayoutEffect = useLayoutEffect;
exports.useMemo = useMemo;
exports.useReducer = useReducer;
exports.useRef = useRef;
exports.useState = useState;
exports.version = ReactVersion;
  })();
}


/***/ }),

/***/ "./node_modules/react/index.js":
/*!*************************************!*\
  !*** ./node_modules/react/index.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


if (false) {} else {
  module.exports = __webpack_require__(/*! ./cjs/react.development.js */ "./node_modules/react/cjs/react.development.js");
}


/***/ }),

/***/ "./src/amz.utils.js":
/*!**************************!*\
  !*** ./src/amz.utils.js ***!
  \**************************/
/*! exports provided: WooZoneNoAWSCategs, WooZoneNoAWS_Country_List, WooZoneNoAWS_isset, WooZoneParser, WooZoneMakeID, WooZoneChunk, WooZoneHumanFileSize, WooZoneGetHostName, WooZoneGetDomain, WooZoneContentTrimmer, WooZoneImportUrlGetDebugStep */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneNoAWSCategs", function() { return WooZoneNoAWSCategs; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneNoAWS_Country_List", function() { return WooZoneNoAWS_Country_List; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneNoAWS_isset", function() { return WooZoneNoAWS_isset; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneParser", function() { return WooZoneParser; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneMakeID", function() { return WooZoneMakeID; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneChunk", function() { return WooZoneChunk; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneHumanFileSize", function() { return WooZoneHumanFileSize; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneGetHostName", function() { return WooZoneGetHostName; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneGetDomain", function() { return WooZoneGetDomain; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneContentTrimmer", function() { return WooZoneContentTrimmer; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "WooZoneImportUrlGetDebugStep", function() { return WooZoneImportUrlGetDebugStep; });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

var $ = jQuery;
function WooZoneNoAWSCategs(country) {
  /*
  //extract script, on amazon country homepage, like: https://www.amazon.nl or https://www.amazon.sg
  
  let country_select = document.querySelector(".nav-search-dropdown");
  let exportjson = {}
  for(let i=0; i<country_select.options.length;i++){
    let cc_value = country_select.options[i].value;
    cc_value = cc_value.replace("", "");
    cc_value = cc_value.replace("search-alias=", "");
    let cc_text = country_select.options[i].innerText;
    exportjson[cc_value] = cc_text;
  }
  console.log( JSON.stringify(exportjson) );
  */
  var data = {
    'com': {
      "aps": "All Departments",
      "arts-crafts-intl-ship": "Arts & Crafts",
      "automotive-intl-ship": "Automotive",
      "baby-products-intl-ship": "Baby",
      "beauty-intl-ship": "Beauty & Personal Care",
      "stripbooks-intl-ship": "Books",
      "computers-intl-ship": "Computers",
      "digital-music": "Digital Music",
      "electronics-intl-ship": "Electronics",
      "digital-text": "Kindle Store",
      "instant-video": "Prime Video",
      "fashion-womens-intl-ship": "Women's Fashion",
      "fashion-mens-intl-ship": "Men's Fashion",
      "fashion-girls-intl-ship": "Girls' Fashion",
      "fashion-boys-intl-ship": "Boys' Fashion",
      "deals-intl-ship": "Deals",
      "hpc-intl-ship": "Health & Household",
      "kitchen-intl-ship": "Home & Kitchen",
      "industrial-intl-ship": "Industrial & Scientific",
      "luggage-intl-ship": "Luggage",
      "movies-tv-intl-ship": "Movies & TV",
      "music-intl-ship": "Music, CDs & Vinyl",
      "pets-intl-ship": "Pet Supplies",
      "software-intl-ship": "Software",
      "sporting-intl-ship": "Sports & Outdoors",
      "tools-intl-ship": "Tools & Home Improvement",
      "toys-and-games-intl-ship": "Toys & Games",
      "videogames-intl-ship": "Video Games"
    },
    'co.uk': {
      "aps": "All Departments",
      "alexa-skills": "Alexa Skills",
      "amazon-devices": "Amazon Devices",
      "amazon-global-store": "Amazon Global Store",
      "pantry": "Amazon Pantry",
      "warehouse-deals": "Amazon Warehouse Deals",
      "mobile-apps": "Apps & Games",
      "baby": "Baby",
      "beauty": "Beauty",
      "stripbooks": "Books",
      "automotive": "Car & Motorbike",
      "popular": "CDs & Vinyl",
      "classical": "Classical Music",
      "clothing": "Clothing",
      "computers": "Computers & Accessories",
      "digital-music": "Digital Music",
      "diy": "DIY & Tools",
      "dvd": "DVD & Blu-ray",
      "electronics": "Electronics & Photo",
      "fashion": "Fashion",
      "outdoor": "Garden & Outdoors",
      "gift-cards": "Gift Cards",
      "grocery": "Grocery",
      "handmade": "Handmade",
      "drugstore": "Health & Personal Care",
      "local-services": "Home & Business Services",
      "kitchen": "Home & Kitchen",
      "industrial": "Industrial & Scientific",
      "jewelry": "Jewellery",
      "digital-text": "Kindle Store",
      "appliances": "Large Appliances",
      "lighting": "Lighting",
      "luggage": "Luggage",
      "luxury-beauty": "Luxury Beauty",
      "mi": "Musical Instruments & DJ",
      "videogames": "PC & Video Games",
      "pets": "Pet Supplies",
      "instant-video": "Prime Video",
      "shoes": "Shoes & Bags",
      "software": "Software",
      "sports": "Sports & Outdoors",
      "office-products": "Stationery & Office Supplies",
      "toys": "Toys & Games",
      "vhs": "VHS",
      "watches": "Watches"
    },
    'de': {
      "aps": "Alle Kategorien",
      "alexa-skills": "Alexa Skills",
      "amazon-devices": "Amazon Geräte",
      "amazon-global-store": "Amazon Global Store",
      "pantry": "Amazon Pantry",
      "warehouse-deals": "Amazon Warehouse Deals",
      "mobile-apps": "Apps & Spiele",
      "automotive": "Auto & Motorrad",
      "baby": "Baby",
      "diy": "Baumarkt",
      "beauty": "Beauty",
      "clothing": "Bekleidung",
      "lighting": "Beleuchtung",
      "stripbooks": "Bücher",
      "english-books": "Bücher (Fremdsprachig)",
      "office-products": "Bürobedarf & Schreibwaren",
      "computers": "Computer & Zubehör",
      "drugstore": "Drogerie & Körperpflege",
      "dvd": "DVD & Blu-ray",
      "appliances": "Elektro-Großgeräte",
      "electronics": "Elektronik & Foto",
      "fashion": "Fashion",
      "videogames": "Games",
      "outdoor": "Garten",
      "gift-cards": "Geschenkgutscheine",
      "industrial": "Gewerbe, Industrie & Wissenschaft",
      "handmade": "Handmade",
      "pets": "Haustier",
      "photo": "Kamera & Foto",
      "digital-text": "Kindle-Shop",
      "classical": "Klassik",
      "luggage": "Koffer, Rucksäcke & Taschen",
      "kitchen": "Küche, Haushalt & Wohnen",
      "grocery": "Lebensmittel & Getränke",
      "luxury-beauty": "Luxury Beauty",
      "popular": "Musik-CDs & Vinyl",
      "digital-music": "Musik-Downloads",
      "mi": "Musikinstrumente & DJ-Equipment",
      "instant-video": "Prime Video",
      "jewelry": "Schmuck",
      "shoes": "Schuhe & Handtaschen",
      "software": "Software",
      "toys": "Spielzeug",
      "sports": "Sport & Freizeit",
      "watches": "Uhren",
      "magazines": "Zeitschriften"
    },
    'fr': {
      "aps": "Toutes nos catégories",
      "alexa-skills": "Alexa Skills",
      "warehouse-deals": "Amazon Offres Reconditionnées",
      "pantry": "Amazon Pantry",
      "pets": "Animalerie",
      "amazon-devices": "Appareils Amazon",
      "mobile-apps": "Applis & Jeux",
      "automotive": "Auto et Moto",
      "luggage": "Bagages",
      "beauty": "Beauté et Parfum",
      "luxury-beauty": "Beauté Prestige",
      "jewelry": "Bijoux",
      "gift-cards": "Boutique chèques-cadeaux",
      "digital-text": "Boutique Kindle",
      "diy": "Bricolage",
      "baby": "Bébés & Puériculture",
      "shoes": "Chaussures et Sacs",
      "kitchen": "Cuisine & Maison",
      "dvd": "DVD & Blu-ray",
      "grocery": "Epicerie",
      "office-products": "Fournitures de bureau",
      "appliances": "Gros électroménager",
      "handmade": "Handmade",
      "electronics": "High-Tech",
      "hpc": "Hygiène et Santé",
      "computers": "Informatique",
      "mi": "Instruments de musique & Sono",
      "garden": "Jardin",
      "toys": "Jeux et Jouets",
      "videogames": "Jeux vidéo",
      "english-books": "Livres anglais et étrangers",
      "stripbooks": "Livres en français",
      "software": "Logiciels",
      "lighting": "Luminaires et Eclairage",
      "fashion": "Mode",
      "watches": "Montres",
      "popular": "Musique : CD & Vinyles",
      "classical": "Musique classique",
      "industrial": "Secteur industriel & scientifique",
      "sports": "Sports et Loisirs",
      "digital-music": "Téléchargement de musique",
      "clothing": "Vêtements et accessoires"
    },
    'co.jp': {
      "aps": "すべてのカテゴリー",
      "amazon-devices": "Amazon デバイス",
      "digital-text": "Kindleストア ",
      "instant-video": "Prime Video",
      "alexa-skills": "Alexaスキル",
      "digital-music": "デジタルミュージック",
      "mobile-apps": "Android アプリ",
      "stripbooks": "本",
      "english-books": "洋書",
      "popular": "ミュージック",
      "classical": "クラシック",
      "dvd": "DVD",
      "videogames": "TVゲーム",
      "software": "PCソフト",
      "computers": "パソコン・周辺機器",
      "electronics": "家電&カメラ",
      "office-products": "文房具・オフィス用品",
      "kitchen": "ホーム&キッチン",
      "pets": "ペット用品",
      "hpc": "ドラッグストア",
      "beauty": "ビューティー",
      "luxury-beauty": "ラグジュアリービューティー",
      "food-beverage": "食品・飲料・お酒",
      "baby": "ベビー&マタニティ",
      "fashion": "ファッション",
      "fashion-womens": "   レディース",
      "fashion-mens": "   メンズ",
      "fashion-baby-kids": "   キッズ＆ベビー",
      "apparel": "服＆ファッション小物",
      "shoes": "シューズ＆バッグ",
      "watch": "腕時計",
      "jewelry": "ジュエリー",
      "toys": "おもちゃ",
      "hobby": "ホビー",
      "mi": "楽器",
      "sporting": "スポーツ&アウトドア",
      "automotive": "車＆バイク",
      "diy": "DIY・工具・ガーデン",
      "appliances": "大型家電",
      "financial": "クレジットカード",
      "gift-cards": "ギフト券",
      "industrial": "産業・研究開発用品",
      "pantry": "Amazonパントリー",
      "warehouse-deals": "Amazonアウトレット"
    },
    'ca': {
      "aps": "All Departments",
      "alexa-skills": "Alexa Skills",
      "amazon-devices": "Amazon Devices",
      "warehouse-deals": "Amazon Warehouse Deals",
      "mobile-apps": "Apps & Games",
      "automotive": "Automotive",
      "baby": "Baby",
      "beauty": "Beauty",
      "stripbooks": "Books",
      "apparel": "Clothing & Accessories",
      "electronics": "Electronics",
      "gift-cards": "Gift Cards",
      "grocery": "Grocery",
      "handmade": "Handmade",
      "hpc": "Health & Personal Care",
      "kitchen": "Home & Kitchen",
      "industrial": "Industrial & Scientific",
      "jewelry": "Jewelry",
      "digital-text": "Kindle Store",
      "french-books": "Livres en français",
      "luggage": "Luggage & Bags",
      "luxury-beauty": "Luxury Beauty",
      "dvd": "Movies & TV",
      "popular": "Music",
      "mi": "Musical Instruments, Stage & Studio",
      "office-products": "Office Products",
      "lawngarden": "Patio, Lawn & Garden",
      "pets": "Pet Supplies",
      "shoes": "Shoes & Handbags",
      "software": "Software",
      "sporting": "Sports & Outdoors",
      "tools": "Tools & Home Improvement",
      "toys": "Toys & Games",
      "videogames": "Video Games",
      "watches": "Watches"
    },
    'cn': {
      "aps": "全部分类",
      "amazon-devices": "亚马逊设备",
      "digital-text": "Kindle商店",
      "mobile-apps": "应用程序和游戏",
      "amazon-global-store": "亚马逊海外购",
      "stripbooks": "图书",
      "music": "音乐",
      "videogames": "游戏/娱乐",
      "video": "音像",
      "software": "软件",
      "audio-visual-education": "教育音像",
      "communications": "手机/通讯",
      "photo-video": "摄影/摄像",
      "electronics": "电子",
      "music-players": "数码影音",
      "computers": "电脑/IT",
      "office-products": "办公用品",
      "home-appliances": "小家电",
      "appliances": " 大家电 ",
      "audio-visual": " 电视/音响 ",
      "home": "家用",
      "home-substore": "家居",
      "kitchen": "厨具",
      "home-improvement": "家居装修",
      "pets": "宠物用品",
      "grocery": "食品",
      "wine": "酒",
      "beauty": "美容化妆",
      "hpc": "个护健康",
      "baby": "母婴用品",
      "toys-and-games": "玩具",
      "sporting": "运动户外休闲",
      "apparel": "服饰箱包",
      "shoes": "鞋靴",
      "watches": "钟表",
      "jewelry": "珠宝首饰",
      "automotive": "汽车用品",
      "mi": "乐器",
      "gift-cards": "礼品卡",
      "luxury-beauty": "LuxuryBeauty高端美妆店",
      "warehouse-deals": "Z实惠"
    },
    'in': {
      "aps": "All Categories",
      "alexa-skills": "Alexa Skills",
      "amazon-devices": "Amazon Devices",
      "fashion": "Amazon Fashion",
      "amazon-global-store": "Amazon Global Store",
      "pantry": "Amazon Pantry",
      "appliances": "Appliances",
      "mobile-apps": "Apps & Games",
      "baby": "Baby",
      "beauty": "Beauty",
      "stripbooks": "Books",
      "automotive": "Car & Motorbike",
      "apparel": "Clothing & Accessories",
      "collectibles": "Collectibles",
      "computers": "Computers & Accessories",
      "electronics": "Electronics",
      "furniture": "Furniture",
      "lawngarden": "Garden & Outdoors",
      "gift-cards": "Gift Cards",
      "grocery": "Grocery & Gourmet Foods",
      "hpc": "Health & Personal Care",
      "kitchen": "Home & Kitchen",
      "industrial": "Industrial & Scientific",
      "jewelry": "Jewellery",
      "digital-text": "Kindle Store",
      "luggage": "Luggage & Bags",
      "luxury-beauty": "Luxury Beauty",
      "dvd": "Movies & TV Shows",
      "popular": "Music",
      "mi": "Musical Instruments",
      "office-products": "Office Products",
      "pets": "Pet Supplies",
      "shoes": "Shoes & Handbags",
      "software": "Software",
      "sporting": "Sports, Fitness & Outdoors",
      "home-improvement": "Tools & Home Improvement",
      "toys": "Toys & Games",
      "videogames": "Video Games",
      "watches": "Watches"
    },
    'it': {
      "aps": "Tutte le categorie",
      "apparel": "Abbigliamento",
      "alexa-skills": "Alexa Skill",
      "grocery": "Alimentari e cura della casa",
      "pantry": "Amazon Pantry",
      "warehouse-deals": "Amazon Warehouse Deals",
      "mobile-apps": "App e Giochi",
      "automotive": "Auto e Moto",
      "beauty": "Bellezza",
      "gift-cards": "Buoni Regalo",
      "office-products": "Cancelleria e prodotti per ufficio",
      "kitchen": "Casa e cucina",
      "popular": "CD e Vinili ",
      "amazon-devices": "Dispositivi Amazon",
      "electronics": "Elettronica",
      "diy": "Fai da te",
      "dvd": "Film e TV",
      "garden": "Giardino e giardinaggio",
      "toys": "Giochi e giocattoli",
      "jewelry": "Gioielli",
      "appliances": "Grandi elettrodomestici",
      "handmade": "Handmade",
      "lighting": "Illuminazione",
      "industrial": "Industria e Scienza",
      "computers": "Informatica",
      "digital-text": "Kindle Store",
      "stripbooks": "Libri",
      "english-books": "Libri in altre lingue",
      "fashion": "Moda",
      "digital-music": "Musica Digitale",
      "watches": "Orologi",
      "baby": "Prima infanzia",
      "pets": "Prodotti per animali domestici",
      "hpc": "Salute e cura della persona",
      "shoes": "Scarpe e borse",
      "software": "Software",
      "sporting": "Sport e tempo libero",
      "mi": "Strumenti musicali e DJ",
      "luggage": "Valigeria",
      "videogames": "Videogiochi"
    },
    'es': {
      "aps": "Todos los departamentos",
      "alexa-skills": "Alexa Skills",
      "grocery": "Alimentación y bebidas",
      "pantry": "Amazon Pantry",
      "mobile-apps": "Appstore para Android",
      "baby": "Bebé",
      "beauty": "Belleza",
      "diy": "Bricolaje y herramientas",
      "gift-cards": "Cheques regalo",
      "automotive": "Coche y moto",
      "sporting": "Deportes y aire libre",
      "amazon-devices": "Dispositivos de Amazon",
      "electronics": "Electrónica",
      "luggage": "Equipaje",
      "appliances": "Grandes electrodomésticos",
      "handmade": "Handmade",
      "kitchen": "Hogar y cocina",
      "lighting": "Iluminación",
      "industrial": "Industria y ciencia",
      "computers": "Informática",
      "mi": "Instrumentos musicales",
      "lawngarden": "Jardín",
      "jewelry": "Joyería",
      "toys": "Juguetes y juegos",
      "stripbooks": "Libros",
      "english-books": "Libros en idiomas extranjeros",
      "fashion": "Moda",
      "digital-music": "Música Digital",
      "popular": "Música: CDs y vinilos",
      "office-products": "Oficina y papelería",
      "dvd": "Películas y TV",
      "pets": "Productos para mascotas",
      "warehouse-deals": "Productos Reacondicionados",
      "watches": "Relojes",
      "apparel": "Ropa y accesorios",
      "hpc": "Salud y cuidado personal",
      "software": "Software",
      "digital-text": "Tienda Kindle",
      "videogames": "Videojuegos",
      "shoes": "Zapatos y complementos"
    },
    'com.mx': {
      "aps": "Todos los departamentos",
      "alexa-skills": "Alexa Skills",
      "automotive": "Auto",
      "baby": "Bebé",
      "amazon-devices": "Dispositivos de Amazon",
      "electronics": "Electrónicos",
      "dvd": "Películas y Series de TV",
      "instant-video": "Prime Video",
      "digital-text": "Tienda Kindle",
      "fashion": "Ropa, Zapatos y Accesorios",
      "fashion-womens": "   Mujeres",
      "fashion-mens": "   Hombres",
      "fashion-girls": "   Niñas",
      "fashion-boys": "   Niños",
      "fashion-baby": "   Bebé",
      "grocery": "Alimentos y Bebidas",
      "sporting": "Deportes y Aire Libre",
      "hi": "Herramientas y Mejoras del Hogar",
      "kitchen": "Hogar y Cocina",
      "industrial": "Industria y ciencia",
      "mi": "Instrumentos musicales",
      "toys": "Juegos y juguetes",
      "stripbooks": "Libros",
      "pets": "Mascotas",
      "popular": "Música",
      "office-products": "Oficina y Papelería",
      "handmade": "Productos Handmade",
      "warehouse-deals": "Remates de Almacén",
      "hpc": "Salud, Belleza y Cuidado Personal",
      "software": "Software",
      "gift-cards": "Tarjetas de Regalo",
      "videogames": "Videojuegos"
    },
    'com.br': {
      "aps": "Todos os departamentos",
      "alexa-skills": "Alexa Skills",
      "grocery": "Alimentos e Bebidas",
      "mobile-apps": "Apps e Jogos",
      "automotive": "Automotivo",
      "baby": "Bebês",
      "beauty": "Beleza",
      "fashion-luggage": "Bolsas, Malas e Mochilas",
      "toys": "Brinquedos e Jogos",
      "home": "Casa",
      "popular": "CD e Vinil",
      "computers": "Computadores e Informática",
      "kitchen": "Cozinha",
      "amazon-devices": "Dispositivos Amazon",
      "dvd": "DVD e Blu-Ray",
      "appliances": "Eletrodomésticos",
      "electronics": "Eletrônicos",
      "sporting": "Esportes e Aventura",
      "hi": "Ferramentas e Materiais de Construção",
      "videogames": "Games",
      "garden": "Jardim e Piscina",
      "stripbooks": "Livros",
      "digital-text": "Loja Kindle",
      "office-products": "Material para Escritório e Papelaria",
      "furniture": "Móveis e Decoração",
      "pets": "Pet Shop",
      "instant-video": "Prime Video",
      "fashion": "Roupas, Calçados e Joias",
      "fashion-womens": "   Feminino",
      "fashion-mens": "   Masculino",
      "fashion-girls": "   Meninas",
      "fashion-boys": "   Meninos",
      "fashion-baby": "   Bebês",
      "hpc": "Saúde e Cuidados Pessoais"
    },
    'com.au': {
      "aps": "All Departments",
      "alexa-skills": "Alexa Skills",
      "amazon-devices": "Amazon Devices",
      "amazon-global-store": "Amazon Global Store",
      "mobile-apps": "Apps & Games",
      "audible": "Audible Audiobooks",
      "automotive": "Automotive",
      "baby": "Baby",
      "beauty": "Beauty",
      "stripbooks": "Books",
      "popular": "CDs & Vinyl",
      "fashion": "Clothing, Shoes & Accessories",
      "fashion-womens": "   Women",
      "fashion-mens": "   Men",
      "fashion-girls": "   Girls",
      "fashion-boys": "   Boys",
      "fashion-baby": "   Baby",
      "computers": "Computers",
      "electronics": "Electronics",
      "gift-cards": "Gift Cards",
      "hpc": "Health, Household & Personal Care",
      "home": "Home",
      "home-improvement": "Home Improvement",
      "digital-text": "Kindle Store",
      "fashion-luggage": "Luggage & Travel Gear",
      "luxury-beauty": "Luxury Beauty",
      "movies-tv": "Movies & TV",
      "grocery": "Pantry Food & Drinks",
      "pets": "Pet Supplies",
      "software": "Software",
      "sporting": "Sports, Fitness & Outdoors",
      "office-products": "Stationery & Office Products",
      "toys": "Toys & Games",
      "videogames": "Video Games"
    },
    'ae': {
      "aps": "All Categories",
      "amazon-devices": "Amazon Devices",
      "fashion": "Amazon Fashion",
      "amazon-global-store": "Amazon Global Store",
      "appliances": "Appliances",
      "automotive": "Automotive Parts & Accessories",
      "baby": "Baby",
      "beauty": "Beauty & Personal Care",
      "stripbooks": "Books",
      "computers": "Computer & Accessories",
      "electronics": "Electronics",
      "gift-cards": "Gift Cards",
      "grocery": "Grocery & Gourmet Food",
      "hpc": "Health, Household & Baby Care",
      "garden": "Home & Garden",
      "kitchen": "Kitchen & Dining",
      "fashion-luggage": "Luggage & Travel Gear",
      "mi": "Musical Instruments",
      "office-products": "Office Products",
      "pets": "Pet Supplies",
      "sports": "Sports",
      "tools": "Tools & Home Improvement",
      "toys": "Toys & Games",
      "videogames": "Video Games"
    },
    "nl": {
      "aps": "Alle afdelingen",
      "amazon-devices": "Amazon-apparaten",
      "automotive": "Auto en motor",
      "baby": "Babyproducten",
      "beauty": "Beauty en persoonlijke verzorging",
      "stripbooks": "Boeken",
      "gift-cards": "Cadeaubonnen",
      "popular": "Cd's en lp's",
      "electronics": "Elektronica",
      "dvd": "Films en tv",
      "hpc": "Gezondheid en persoonlijke verzorging",
      "pets": "Huisdierbenodigdheden",
      "office-products": "Kantoorproducten",
      "digital-text": "Kindle Store",
      "fashion": "Kleding, schoenen en sieraden",
      "home-improvement": "Klussen en gereedschap",
      "grocery": "Levensmiddelen",
      "mi": "Muziekinstrumenten",
      "misc": "Overig",
      "instant-video": "Prime Video",
      "software": "Software",
      "toys": "Speelgoed en spellen",
      "sports": "Sport en outdoor",
      "outdoor": "Tuin, terras en gazon",
      "videogames": "Videogames",
      "home": "Wonen en keuken",
      "industrial": "Zakelijk, industrie en wetenschap"
    },
    "sg": {
      "aps": "All Departments",
      "amazon-global-store": "Amazon International Store",
      "automotive": "Automotive",
      "baby": "Baby",
      "beauty": "Beauty & Personal Care",
      "stripbooks": "Books",
      "fashion": "Clothing, Shoes & Jewelry",
      "computers": "Computer & Accessories",
      "electronics": "Electronics",
      "grocery": "Grocery",
      "hpc": "Health, Household & Personal Care",
      "home": "Home",
      "kitchen": "Kitchen & Dining",
      "office-products": "Office Products",
      "pets": "Pet Supplies",
      "sporting": "Sports & Outdoors",
      "home-improvement": "Tools & Home Improvement",
      "toys": "Toys & Games",
      "videogames": "Video Games"
    },
    "sa": {
      "aps": "جميع الأقسام",
      "mi": "آلات موسيقية",
      "amazon-devices": "أجهزة Amazon",
      "home-improvement": "أدوات وتحسينات المنزل",
      "fashion": "أزياء Amazon",
      "videogames": "ألعاب الفيديو",
      "toys": "الألعاب والدمى",
      "electronics": "الإلكترونيات",
      "grocery": "البقالة والطعام الفاخر",
      "beauty": "الجمال والعناية الشخصية",
      "sports": "الرياضة",
      "hpc": "الصحة، الأسرة والعناية بالطفل",
      "industrial": "الصناعة والعلم",
      "arts-crafts": "الفنون والحرف والخياطة",
      "kitchen": "المطبخ والطعام",
      "garden": "المنزل والحديقة",
      "baby": "طفل",
      "automotive": "قطع وإكسسوارات السيارات",
      "stripbooks": "كتب",
      "amazon-global-store": "متجر أمازون العالمي",
      "pets": "مستلزمات الحيوانات الأليفة",
      "office-products": "منتجات المكتب",
      "home": "منتجات المنزل"
    },
    "com.tr": {
      "aps": "Tüm Kategoriler",
      "garden": "Bahçe",
      "baby": "Bebek",
      "computers": "Bilgisayarlar",
      "electronics": "Elektronik",
      "home": "Ev",
      "kitchen": "Ev ve Mutfak",
      "pets": "Evcil Hayvan Malzemeleri",
      "gift-cards": "Hediye Kartları",
      "stripbooks": "Kitaplar",
      "beauty": "Kişisel Bakım ve Kozmetik",
      "fashion": "Moda",
      "mi": "Müzik Aletleri",
      "office-products": "Ofis Ürünleri",
      "toys": "Oyuncaklar ve Oyunlar",
      "videogames": "PC ve Video Oyunları",
      "instant-video": "Prime Video",
      "hpc": "Sağlık ve Bakım",
      "sports": "Spor",
      "diy": "Yapı Market"
    },
    "se": {
      "aps": "Alla kategorier",
      "amazon-devices": "Amazon-enheter",
      "baby": "Babyprodukter",
      "home-improvement": "Bygg, el & verktyg",
      "stripbooks": "Böcker",
      "electronics": "Elektronik",
      "movies-tv": "Film & TV-serier",
      "automotive": "Fordon",
      "home": "Hem & kök",
      "arts-crafts": "Hobby & hantverk",
      "pets": "Husdjursprodukter",
      "hpc": "Hälsa, vård & hushåll",
      "industrial": "Industriella verktyg & produkter",
      "fashion": "Kläder, skor & accessoarer",
      "office-products": "Kontorsprodukter & skolmaterial",
      "toys": "Leksaker & spel ",
      "popular": "Musik",
      "mi": "Musikinstrument",
      "gift-cards": "Presentkort",
      "instant-video": "Prime Video",
      "software": "Programvara",
      "beauty": "Skönhet & kroppsvård",
      "sporting": "Sport & outdoor",
      "garden": "Trädgård",
      "videogames": "TV-spel & konsoler"
    },
    "pl": {
      "aps": "Wszystkie kategorie",
      "arts-crafts": "Arts & crafts",
      "office-products": "Biuro",
      "industrial": "Biznes, przemysł i nauka",
      "home": "Dom i kuchnia",
      "baby": "Dziecko",
      "electronics": "Elektronika",
      "movies-tv": "Filmy i programy TV",
      "videogames": "Gry wideo",
      "mi": "Instrumenty muzyczne",
      "gift-cards": "Karty podarunkowe",
      "computers": "Komputery i akcesoria",
      "stripbooks": "Książki",
      "automotive": "Motoryzacja",
      "popular": "Muzyka",
      "fashion": "Odzież, obuwie i akcesoria",
      "garden": "Ogród",
      "software": "Oprogramowanie",
      "home-improvement": "Renowacja domu",
      "sporting": "Sport i turystyka",
      "beauty": "Uroda",
      "amazon-devices": "Urządzenia Amazon",
      "toys": "Zabawki i gry",
      "hpc": "Zdrowie i gospodarstwo domowe",
      "pets": "Zwierzęta"
    }
  };
  return WooZoneNoAWS_isset(data, country) ? data[country] : [];
}
function WooZoneNoAWS_Country_List() {
  return [{
    'alias': 'com',
    'label': 'United States',
    'flag': 'assets/flags/US.gif'
  }, {
    'alias': 'co.uk',
    'label': 'United Kingdom',
    'flag': 'assets/flags/UK.gif'
  }, {
    'alias': 'de',
    'label': 'Deutschland',
    'flag': 'assets/flags/DE.gif'
  }, {
    'alias': 'fr',
    'label': 'France',
    'flag': 'assets/flags/FR.gif'
  }, {
    'alias': 'co.jp',
    'label': 'Japan',
    'flag': 'assets/flags/JP.gif'
  }, {
    'alias': 'ca',
    'label': 'Canada',
    'flag': 'assets/flags/CA.gif'
  }, {
    'alias': 'cn',
    'label': 'China',
    'flag': 'assets/flags/CN.gif'
  }, {
    'alias': 'in',
    'label': 'India',
    'flag': 'assets/flags/IN.gif'
  }, {
    'alias': 'it',
    'label': 'Italia',
    'flag': 'assets/flags/IT.gif'
  }, {
    'alias': 'es',
    'label': 'España',
    'flag': 'assets/flags/ES.gif'
  }, {
    'alias': 'com.mx',
    'label': 'Mexico',
    'flag': 'assets/flags/MX.jpg'
  }, {
    'alias': 'com.br',
    'label': 'Brazil',
    'flag': 'assets/flags/BR.gif'
  }, {
    'alias': 'com.au',
    'label': 'Australia',
    'flag': 'assets/flags/AU.png'
  }, {
    'alias': 'ae',
    'label': 'UAE',
    'flag': 'assets/flags/UAE.gif'
  }, {
    'alias': 'nl',
    'label': 'Netherlands',
    'flag': 'assets/flags/NL.gif'
  }, {
    'alias': 'sg',
    'label': 'Singapore',
    'flag': 'assets/flags/SG.gif'
  }, {
    'alias': 'sa',
    'label': 'Saudi Arabia',
    'flag': 'assets/flags/SA.gif'
  }, {
    'alias': 'com.tr',
    'label': 'Turkey',
    'flag': 'assets/flags/TR.gif'
  }, {
    'alias': 'se',
    'label': 'Sweden',
    'flag': 'assets/flags/SE.gif'
  }, {
    'alias': 'pl',
    'label': 'Poland',
    'flag': 'assets/flags/PL.gif'
  }];
}
function WooZoneNoAWS_isset(arr, var_args) {
  for (var i = 1, k = arguments.length; i < k; ++i) {
    if (!arr || !arr.hasOwnProperty(arguments[i])) return false;
    arr = arr[arguments[i]];
  }

  return true;
}
function WooZoneParser(alias, content, _ref) {
  var _ref$lang = _ref.lang,
      lang = _ref$lang === void 0 ? 'en' : _ref$lang,
      _ref$prevState = _ref.prevState,
      prevState = _ref$prevState === void 0 ? {} : _ref$prevState;
  console.log('WooZoneParser: ', alias, lang, prevState);
  var base = {};

  if (alias !== 'paged') {
    return base;
  }

  var isfrom = WooZoneNoAWS_isset(prevState, 'isfrom') ? prevState['isfrom'] : '';
  var prevPerPage = WooZoneNoAWS_isset(prevState, 'pagination', 'per_page') ? prevState['pagination']['per_page'] : -999;
  var $content = $(content);
  var $breadcrumb = $content.find(".s-breadcrumb");
  var breadcrumbText = $breadcrumb.text();
  breadcrumbText = $.trim(breadcrumbText);
  var paginationWrapperExp = '.s-result-list.s-search-results .s-result-item ul.a-pagination';
  var $paginationWrapper = $content.find("".concat(paginationWrapperExp));
  var lastPage = -1;

  if ($paginationWrapper.find('li.a-last') && $paginationWrapper.find('li.a-last').prev()) {
    lastPage = $paginationWrapper.find('li.a-last').prev().text();
    lastPage = parseInt(lastPage, 10);
  }

  console.log('WooZoneParser: ', alias, $paginationWrapper, lastPage, $paginationWrapper.find('li.a-last').prev()); // [update] on 2020-nov-30
  // ex: 1-16 of over 100,000 results for "lcd"

  var max = 20; //let regex = /(\d+)(?:-|–| a )(\d+) .* (\d+(?:(?:[\.,\s]{1})\d+)?\s)/gmi;

  var regex = /(\d+)(?:-|–| a )(\d+) (?:[^\d]+) (\d+(?:(?:[\.,\s]{1})\d+)?)\s/gmi;

  if ('cn' === lang) {
    max = 10;
    regex = /(\d+)-(\d+)条， 共超过(\d+(?:(?:[\.,]{1})\d+)?)/gmi;
  } else if ('co.jp' === lang) {
    max = 10;
    regex = /(\d+(?:(?:[\.,]{1})\d+)?)(?: 以上 のうち | のうち )(\d+)-(\d+)/gm;
  } else if ('com.tr' === lang) {
    max = 10;
    regex = /(\d+(?:(?:[\.,]{1})\d+)?)(?:[^\d]+)(\d+)-(\d+)/gm;
  }

  var matches = regex.exec(breadcrumbText);
  console.log('WooZoneParser: ', alias, matches);

  if (!matches || !matches.length) {
    return base;
  } //:: start, end, totals


  if (WooZoneNoAWS_isset(matches, 1)) {
    if ('co.jp' === lang || 'com.tr' === lang) {
      base['totals'] = parseInt(matches[1].replace(/[\.,\s ]+/gi, ''));
    } else {
      base['start'] = parseInt(matches[1]);
    }
  }

  if (WooZoneNoAWS_isset(matches, 2)) {
    if ('co.jp' === lang || 'com.tr' === lang) {
      base['start'] = parseInt(matches[2]);
    } else {
      base['end'] = parseInt(matches[2]);
    }
  }

  if (WooZoneNoAWS_isset(matches, 3)) {
    if ('co.jp' === lang || 'com.tr' === lang) {
      base['end'] = parseInt(matches[3]);
    } else {
      base['totals'] = parseInt(matches[3].replace(/[\.,\s ]+/gi, ''));
    }
  } //:: per page


  if (WooZoneNoAWS_isset(base, 'start') && WooZoneNoAWS_isset(base, 'end')) {
    base['per_page'] = parseInt(base['end'] + 1 - base['start']);

    if ('pagination' === isfrom && prevPerPage > 0 && base['per_page'] !== prevPerPage) {
      base['per_page'] = prevPerPage;
    }
  } //:: totals


  if (WooZoneNoAWS_isset(base, 'per_page') && lastPage > 0) {
    var tmp = parseInt(lastPage * base['per_page'], 10);

    if (!WooZoneNoAWS_isset(base, 'totals')) {
      base['totals'] = tmp;
    } else if (base['totals'] > tmp) {
      base['totals'] = tmp;
    }
  } //:: FIXES


  if (WooZoneNoAWS_isset(base, 'totals') && WooZoneNoAWS_isset(base, 'per_page')) {
    var _tmp = parseInt(max * base['per_page'], 10);

    if (base['totals'] > _tmp) {
      base['totals'] = _tmp;
    }
  }

  if (WooZoneNoAWS_isset(base, 'end') && WooZoneNoAWS_isset(base, 'totals')) {
    if (base['end'] >= base['totals']) {
      delete base['per_page'];
    }
  }

  console.log('WooZoneParser: ', alias, breadcrumbText, matches, base);
  return base;
}
function WooZoneMakeID() {
  var length = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 10;
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

  for (var i = 0; i < length; i++) {
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  }

  return text;
}
function WooZoneChunk(arr, chunkSize) {
  var R = [];

  for (var i = 0, len = arr.length; i < len; i += chunkSize) {
    R.push(arr.slice(i, i + chunkSize));
  }

  return R;
}
function WooZoneHumanFileSize(bytes, si) {
  var thresh = si ? 1000 : 1024;

  if (Math.abs(bytes) < thresh) {
    return bytes + ' B';
  }

  var units = si ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
  var u = -1;

  do {
    bytes /= thresh;
    ++u;
  } while (Math.abs(bytes) >= thresh && u < units.length - 1);

  return bytes.toFixed(1) + ' ' + units[u];
}
function WooZoneGetHostName(url) {
  var match = url.match(/:\/\/(www[0-9]?\.)?(.[^/:]+)/i);

  if (match != null && match.length > 2 && typeof match[2] === 'string' && match[2].length > 0) {
    return match[2];
  } else {
    return null;
  }
}
function WooZoneGetDomain(url) {
  var hostName = WooZoneGetHostName(url);
  var domain = hostName;

  if (hostName != null) {
    var parts = hostName.split('.').reverse();

    if (parts != null && parts.length > 1) {
      domain = parts[1] + '.' + parts[0];

      if (hostName.toLowerCase().indexOf('.co.uk') != -1 && parts.length > 2) {
        domain = parts[2] + '.' + domain;
      }

      if (hostName.toLowerCase().indexOf('.co.jp') != -1 && parts.length > 2) {
        domain = parts[2] + '.' + domain;
      }

      if (hostName.toLowerCase().indexOf('.com.au') != -1 && parts.length > 2) {
        domain = parts[2] + '.' + domain;
      }

      if (hostName.toLowerCase().indexOf('.com.mx') != -1 && parts.length > 2) {
        domain = parts[2] + '.' + domain;
      }

      if (hostName.toLowerCase().indexOf('.com.br') != -1 && parts.length > 2) {
        domain = parts[2] + '.' + domain;
      }

      if (hostName.toLowerCase().indexOf('.com.tr') != -1 && parts.length > 2) {
        domain = parts[2] + '.' + domain;
      }
    }
  }

  return domain;
}
function WooZoneContentTrimmer() {
  var DOM = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';

  function clean_text(content) {
    content = content.replace(/\s\s+/g, ' ');
    content = content.trim();
    return content;
  } // console.time('contentTrimmer');


  DOM = clean_text(DOM);
  var page = $('<div/>').html(DOM).contents().parent(); // some unwanted HTML tags

  page.find("header").remove();
  page.find("style").remove();
  page.find("link").remove();
  page.find("input").remove();
  page.find("select").remove(); // footer large menu

  page.find("#navFooter").remove(); // reviews

  page.find("#reviewsMedley").remove(); // Frequently bought together

  page.find("#sims-consolidated-1_feature_div").remove(); // Sponsored products related to this item

  page.find("#sims-consolidated-2_feature_div").remove(); // Customers who bought this item also bought

  page.find("#sims-consolidated-3_feature_div").remove(); // Sponsored products related to this item

  page.find("#sponsoredProducts2_feature_div").remove();
  page.find("*").removeAttr('style');
  page.find("script").each(function () {
    var that = $(this);

    if (that.text().indexOf("twister-js-init-dpx-data") == -1) {
      that.remove();
    }
  }); // console.timeEnd('contentTrimmer');

  return page.html();
}
function WooZoneImportUrlGetDebugStep() {
  var ret = {
    status: 'invalid',
    value: '',
    asparam: ''
  };
  var queryString = window.location.search; //console.log('queryString', queryString);

  var urlParams = new URLSearchParams(queryString); //console.log('debug_step', urlParams.has('debug_step'));

  if (urlParams.has('debug_step')) {
    var debug_step = urlParams.get('debug_step'); //console.log('debug_step', debug_step);

    ret = _objectSpread({}, ret, {
      status: 'valid',
      value: debug_step,
      asparam: "&debug_step=".concat(debug_step)
    });
  }

  return ret;
}

/***/ }),

/***/ "./src/app.jsx":
/*!*********************!*\
  !*** ./src/app.jsx ***!
  \*********************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var react_paginate__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react-paginate */ "./node_modules/react-paginate/dist/index.js");
/* harmony import */ var react_paginate__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react_paginate__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _country_dropdown_class_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./country-dropdown.class.js */ "./src/country-dropdown.class.js");
/* harmony import */ var _amz_utils_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./amz.utils.js */ "./src/amz.utils.js");







function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _createSuper(Derived) { return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(Derived), result; if (_isNativeReflectConstruct()) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_4___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_3___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

var _wp$element = wp.element,
    Component = _wp$element.Component,
    render = _wp$element.render,
    findDOMNode = _wp$element.findDOMNode,
    Fragment = _wp$element.Fragment;
var $ = jQuery;
var _wp$components = wp.components,
    RangeControl = _wp$components.RangeControl,
    CheckboxControl = _wp$components.CheckboxControl;
 //import md5 from 'js-md5';



var alias = 'WooZone-NAWS-';
var image_block_wrapper = document.getElementById("".concat(alias, "wrapper"));

var WooZoneNoAWSImport = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_5___default()(WooZoneNoAWSImport, _Component);

  var _super = _createSuper(WooZoneNoAWSImport);

  function WooZoneNoAWSImport(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1___default()(this, WooZoneNoAWSImport);

    _this = _super.call(this, props);
    _this.DEBUG = false;
    _this.state = {
      ipc_is_valid: false,
      show_log: true,
      country: 'com',
      categ: 'aps',
      keyword: '',
      search_sort: 'relevanceblender',
      search_result_page: null,
      show_main_loader: false,
      pagination: {},
      products: {},
      paged: 1,
      import_in_progress: false,
      products_get_in_progress: [],
      selected_products: {},
      imported_products: {},
      import_setup: {
        variations: 20,
        images: 20,
        attributes: true,
        spin: false,
        threads: 2,
        type: 'simple'
      },
      logs: {},
      view_log_asin: false,
      fakeUpdateState: 0,
      is_chrome: navigator.userAgent.toLowerCase().indexOf('chrome') > -1,
      extention_loaded: false,
      ipc_valid: false,
      workers: {},
      quick_menu: 'close',
      isfrom: '' // to identify if the click come from pagination links or just hit the search button

    };
    _this.nbProducts = 0;
    _this.searchFormRef = React.createRef();
    _this.searchFieldPaged = React.createRef(); // debug
    // if( 0 ) {
    //   setTimeout( () => {
    //     document.getElementById("WooZone-NAWS-search-form").elements[4].click();
    //     if( 0 ){
    //       setTimeout( () => {
    //         let products = document.getElementsByClassName("WooZone-NAWS-get-product")
    //         Array.from(products).slice(10, 10).forEach( (elm, index) => {
    //           elm.click();
    //         })
    //         setTimeout( () => {
    //           document.getElementById("WooZone-NAWS-import-form").elements[9].click();
    //         }, 300 )
    //       }, 500 )
    //     }
    //   }, 500 )
    // }

    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2___default()(WooZoneNoAWSImport, [{
    key: "custom_hash_translate",
    value: function custom_hash_translate(str) {
      if (typeof str == "undefined") return false;
      return window.atob(str).replace('#-1#', "a").replace('#-5#', "b").replace('#-6#', "c").replace('#-4#', "h").replace('#-21#', "t").replace('#-76#', "w");
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      var self = this; //console.log( 'urlGetDebugStep', urlGetDebugStep() );

      window.addEventListener("message", function (event) {
        if (event.data.type && event.data.type == "".concat(alias, "response")) {
          var _event$data = event.data,
              action = _event$data.action,
              sub_action = _event$data.sub_action,
              response = _event$data.response,
              url = _event$data.url;
          console.log(action, sub_action, response, url);

          if (action == 'extension-loaded') {
            if (Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_isset"])(response, 'version')) {
              if (parseFloat(response.version) >= 2) {
                var de_hash = self.custom_hash_translate(response.hash);
                console.log(de_hash);
                self.setState({
                  extention_loaded: true,
                  ipc_is_valid: de_hash == WooZoneNoAwsKeysImport.validation.home_url ? true : false
                });
              }
            }
          }

          if (action == 'url-get-content-response') {
            if (sub_action == 'search') {
              self.setState(function (prevState) {
                var newState = {
                  show_main_loader: false,
                  search_result_page: response.result,
                  pagination: _objectSpread({}, prevState.pagination, {}, Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneParser"])('paged', response.result, {
                    lang: self.state.country,
                    prevState: prevState
                  }))
                };
                return newState;
              });
            } else if (sub_action == 'variations') {
              if (Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_isset"])(response, 'call_id')) {
                if (self.state.products_get_in_progress.includes(response.call_id)) {
                  self.setProductsVariations(response.call_id, response.result);
                }
              }
            } else if (sub_action == 'validation') {
              if (Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_isset"])(response, 'call_id')) {
                if (response.result.status == 'invalid') {
                  alert("Unable to validate your request. Please contact AA-Team support!");
                }

                self.setState({
                  show_main_loader: false
                });

                if (response.result.status == 'valid') {
                  //self.setState({ ipc_is_valid: true });
                  // refresh the page here!
                  window.location.reload();
                  return true;
                }
              }
            } else if (sub_action == 'product_page') {
              // to do
              if (Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_isset"])(response, 'call_id')) {
                if (self.state.products_get_in_progress.includes(response.call_id)) {
                  // trim unwanted page content, make the request smaller and faster
                  response.result = Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneContentTrimmer"])(response.result);
                  self.getProductMininalData(response.call_id, response.result, url);
                }
              }
            } else {
              if (self.DEBUG) console.log("Invalid sub_action: ".concat(sub_action));
            }
          }
        }
      });
      this.importWorker();
    }
  }, {
    key: "setProductsVariations",
    value: function setProductsVariations(asin, DOM) {
      var variationsJSON = JSON.parse($.trim(DOM));

      if (variationsJSON) {
        this.add_to_log(asin, "Variations prices found: ".concat(variationsJSON.length, " items"), 'success');

        var products = _objectSpread({}, this.state.products);

        if (Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_isset"])(products, asin)) {
          products[asin].variations = variationsJSON;
          this.setState({
            products: products
          });
          this.sendToWZoneImport();
        } else {
          if (this.DEBUG) console.log("For no reason unable to find your asin into state products!");
        }
      } else {
        this.add_to_log(asin, "Unable to find any price for your variations", 'error');
      }

      this.fakeUpdateState();
    }
  }, {
    key: "fakeUpdateState",
    value: function fakeUpdateState() {
      this.setState({
        fakeUpdateState: Math.floor(Math.random() * 999) + 1
      });
    }
  }, {
    key: "sendToWZoneImport",
    value: function sendToWZoneImport() {
      var _this$state = this.state,
          selected_products = _this$state.selected_products,
          products = _this$state.products,
          imported_products = _this$state.imported_products;
      var self = this;
      if (self.DEBUG) console.log("it's time to send to WZone and import the products:");

      if (Object.keys(products).length > 0) {
        if (selected_products[Object.keys(products)[0]]) {
          var asin = Object.keys(products)[0]; // debug, to give error. This will not allow the script to import product

          if (0) {}

          var ourdata = {
            asin: asin,
            country: products[asin].domain.replace("amazon.", ""),
            idcateg: document.getElementById("WooZone-to-category").value == '-1' ? 0 : document.getElementById("WooZone-to-category").value,
            nbimages: this.state.import_setup.images,
            nbvariations: this.state.import_setup.variations,
            spin: this.state.import_setup.spin ? 1 : 0,
            attributes: this.state.import_setup.attributes ? 1 : 0,
            where: 'react',
            variations: Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_isset"])(products, asin, 'variations') ? JSON.stringify(products[asin].variations) : 0,
            page_content: products[asin].DOM
          };
          console.log(ourdata.variations);
          if (self.DEBUG) console.log('our import params', ourdata); // remove the first product from state products

          delete products[asin];
          self.setState({
            products: products
          }); // DEBUG IN FIDDLER

          var theAjaxUrl = "".concat(ajaxurl, "?action=WooZoneNoAWSImport&sub_action=add_product").concat(Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneImportUrlGetDebugStep"])().asparam);
          $.ajax({
            method: "POST",
            url: theAjaxUrl,
            data: ourdata,
            dataType: "json"
          }).done(function (response) {
            if (self.DEBUG) console.log('add product response: ', response);

            if (response.status == 'invalid') {
              self.add_to_log(asin, "INVALID response", 'error');
              self.add_to_log(asin, response.msg, 'error');

              if (response.msg_arr.length) {
                response.msg_arr.forEach(function (elm) {
                  self.add_to_log(asin, elm, 'error');
                });
              }

              self.setState({
                imported_products: _objectSpread({}, self.state.imported_products, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, asin, {
                  status: 'invalid'
                }))
              });
            }

            if (response.status == 'valid') {
              self.add_to_log(asin, "VALID response", 'success');
              self.add_to_log(asin, response.msg, 'success');

              if (response.msg_arr.length) {
                response.msg_arr.forEach(function (elm) {
                  self.add_to_log(asin, elm, 'notice');
                });
              }

              self.setState({
                imported_products: _objectSpread({}, self.state.imported_products, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, asin, {
                  status: 'valid'
                }))
              });
            } // setup the total time


            self.add_to_log(asin, 'Total execution time: ', 'end');
            WooZoneNoAwsKeysImport.imported_products.asins_imported += "amz-".concat(asin);
            delete selected_products[asin]; // update the state

            self.setState({
              selected_products: selected_products
            }); // unblock the worker

            self.unblockWorker(asin);
          });
        }
      }
    }
  }, {
    key: "getProductMininalData",
    value: function getProductMininalData(asin, DOM, url) {
      var import_setup = this.state.import_setup;
      var size = Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneHumanFileSize"])(DOM.length);
      this.add_to_log(asin, "page size after content trimmed: <strong>".concat(size, "</strong>"), 'notice');

      if (DOM.length > 100) {
        this.add_to_log(asin, "DOM response looks good", 'success');
      } else {
        this.add_to_log(asin, "response looks INVALID", 'error');
      }

      var productType = 'simple';

      if (DOM.includes('dimensionToAsinMap') && import_setup.type != 'simple') {
        productType = 'variable';
      }

      this.add_to_log(asin, "Product type is ".concat(productType), 'notice');

      if (productType == 'variable') {
        var detected_asin = DOM.split('"currentAsin" : "').pop().split('",')[0];
        var parent_asin = DOM.split('"parentAsin" : "').pop().split('",')[0];

        if (detected_asin == asin || parent_asin == asin) {
          this.add_to_log(asin, "Valid ASIN code: ".concat(detected_asin, " ").concat(parent_asin == asin ? ' as parent.' : ''), 'success');
        } else {
          this.add_to_log(asin, "INVALID ASIN code detected: ".concat(detected_asin), 'error');
        }

        var variations = JSON.parse($.trim(DOM.split('"dimensionToAsinMap" :').pop().split('},')[0] + "}"));
        var uniqVariations = [];
        Object.keys(variations).map(function (object, index) {
          //if( 0 && variations[object] != asin ){
          uniqVariations.push(variations[object]); //}
        });

        if (import_setup.variations < uniqVariations.length) {
          this.add_to_log(asin, "Number of variations setup is: ".concat(import_setup.variations, " and the product has ").concat(uniqVariations.length, " so ").concat(uniqVariations.length - import_setup.variations, " will be ignored."), 'notice');
        }

        uniqVariations = uniqVariations.slice(0, import_setup.variations);

        if (uniqVariations.length == 0) {
          this.add_to_log(asin, "No variations for: ".concat(asin), 'notice'); // import this product as a wocoomerce simple product

          productType = 'simple';
        } else {
          this.add_to_log(asin, "=> Request for the following variations: ".concat(uniqVariations.join(', ')), 'notice');
          var request_url = "https://www.".concat(Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneGetDomain"])(url), "/gp/p13n-shared/faceout-partial?reftagPrefix=homepage&widgetTemplateClass=PI::Similarities::ViewTemplates::Carousel::Desktop&faceoutTemplateClass=PI::P13N::ViewTemplates::Product::Desktop::CarouselFaceout&productDetailsTemplateClass=PI::P13N::ViewTemplates::ProductDetails::Desktop::Base&offset=0&asins=").concat(uniqVariations.join(','));
          if (self.DEBUG) console.log('widget get variations', request_url);
          window.postMessage({
            type: "".concat(alias, "transporter"),
            action: 'url-get-content',
            params: {
              'sub_action': 'variations',
              'url': request_url,
              'call_id': asin,
              'delay': Math.floor(Math.random() * 50) + 1,
              'home_url': WooZoneNoAwsKeysImport.validation.home_url
            }
          });
        }
      }

      this.setState({
        products: _objectSpread({}, this.state.products, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, asin, {
          DOM: DOM,
          size: size,
          domain: Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneGetDomain"])(url),
          type: productType,
          title: DOM.match(/<title[^>]*>([^<]+)<\/title>/)[1],
          satus: productType == 'simple' ? 'done' : 'waiting_for_variations'
        }))
      });

      if (productType == 'simple') {
        this.sendToWZoneImport();
      }
    }
  }, {
    key: "buildCateg",
    value: function buildCateg() {
      var _this2 = this;

      var categ = this.state.categ;
      var categs = Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWSCategs"])(this.state.country);

      if (categs.length == 0) {
        return /*#__PURE__*/React.createElement("div", {
          "class": "notice notice-error"
        }, this.state.country, " not implemented yet!");
      }

      return /*#__PURE__*/React.createElement("select", {
        id: "".concat(alias, "filed-categ"),
        value: categ,
        onChange: function onChange(e) {
          e.preventDefault();

          _this2.setState({
            categ: e.target.value,
            isfrom: ''
          });
        }
      }, Object.keys(categs).map(function (key, index) {
        return /*#__PURE__*/React.createElement("option", {
          key: index,
          value: key
        }, categs[key]);
      }));
    }
  }, {
    key: "addProductBtn",
    value: function addProductBtn(asin, event) {
      this.setState({
        selected_products: _objectSpread({}, this.state.selected_products, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, asin, this.search_results_products[asin]))
      });
    }
  }, {
    key: "buildSearchResults",
    value: function buildSearchResults() {
      var _this$state2 = this.state,
          search_result_page = _this$state2.search_result_page,
          country = _this$state2.country,
          selected_products = _this$state2.selected_products;
      var self = this;

      if (search_result_page) {
        if (Object.keys(this.search_results_products).length == 0) {
          var page_doc = $(search_result_page);
          var cc = 0;
          page_doc.find("div.s-result-list div.s-result-item").each(function () {
            var item = $(this); // skip if is sponsored

            if (item.html().indexOf('sp-sponsored-result') == '-1') {
              cc++;
              var asin = item.data('asin'),
                  image = item.find(".s-image").attr('src'),
                  name = $.trim(item.find('h5').text()),
                  price = item.find(".a-price").html(),
                  rating = item.find(".a-icon-star-small").text(),
                  brand = item.find('h5').next('div').text();
              var variation_asin = '';
              var asin_regex = /\/dp\/(.*)\//gm;
              var match = asin_regex.exec(item.html());

              if (match && match.length > 0) {
                if (Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_isset"])(match, 1)) {
                  variation_asin = match[1];
                }
              }

              if (asin
              /*&& cc < 10*/
              ) {
                  self.search_results_products[asin] = {
                    country: country,
                    variation_asin: variation_asin,
                    name: name,
                    price: price,
                    image: image.replace(/._(.*)_./, '._SS200_.'),
                    brand: brand,
                    rating: rating,
                    url: "https://www.amazon.".concat(country, "/dp/").concat(asin),
                    variation_url: "https://www.amazon.".concat(country, "/dp/").concat(variation_asin)
                  };
                }
            }
          });
        }

        if (Object.keys(this.search_results_products).length) {
          // reset the nb of products for each new search
          this.nbProducts = 0;
          return /*#__PURE__*/React.createElement("div", {
            className: "".concat(alias, "search-results")
          }, /*#__PURE__*/React.createElement("h2", null, "Search Results:"), this.buildPagination(), /*#__PURE__*/React.createElement("ul", null, Object.keys(this.search_results_products).map(function (key, index) {
            self.nbProducts++;
            var already_imported = false;

            if (WooZoneNoAwsKeysImport.imported_products.status == 'valid' && WooZoneNoAwsKeysImport.imported_products.asins_imported.includes("amz-".concat(key))) {
              already_imported = true;
            }

            self.search_results_products[key]['already_imported'] = already_imported;
            return /*#__PURE__*/React.createElement("li", null, /*#__PURE__*/React.createElement("a", {
              href: self.search_results_products[key].url,
              target: "_blank"
            }, /*#__PURE__*/React.createElement("img", {
              src: self.search_results_products[key].image
            })), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h5", null, /*#__PURE__*/React.createElement("a", {
              href: self.search_results_products[key].url,
              target: "_blank"
            }, self.search_results_products[key].name)), /*#__PURE__*/React.createElement("h4", null, self.search_results_products[key].brand), /*#__PURE__*/React.createElement("div", {
              className: "".concat(alias, "price")
            }, self.search_results_products[key].price ? /*#__PURE__*/React.createElement("div", {
              dangerouslySetInnerHTML: {
                __html: self.search_results_products[key].price
              }
            }) : ''), /*#__PURE__*/React.createElement("h4", null, self.search_results_products[key].rating), already_imported == true ? /*#__PURE__*/React.createElement("span", {
              className: "".concat(alias, "already-imported")
            }, "Already Imported") : Object.keys(selected_products).includes(key) ? /*#__PURE__*/React.createElement("span", {
              className: "".concat(alias, "data-added")
            }, "product added to import list") : /*#__PURE__*/React.createElement("button", {
              "data-asin": key,
              className: "".concat(alias, "get-product"),
              onClick: self.addProductBtn.bind(self, key)
            }, "Add to import list")));
          })), this.buildPagination());
        }
      }
    }
  }, {
    key: "buildPagination",
    value: function buildPagination() {
      var _this3 = this;

      return /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "pagination-wrapper")
      }, /*#__PURE__*/React.createElement(react_paginate__WEBPACK_IMPORTED_MODULE_6___default.a, {
        previousLabel: /*#__PURE__*/React.createElement("span", {
          "class": "dashicons dashicons-arrow-left-alt2"
        }),
        nextLabel: /*#__PURE__*/React.createElement("span", {
          "class": "dashicons dashicons-arrow-right-alt2"
        }),
        breakLabel: '...',
        breakClassName: 'break-me',
        pageCount: Math.ceil(this.state.pagination.totals / this.state.pagination.per_page),
        marginPagesDisplayed: 2,
        pageRangeDisplayed: 3,
        onPageChange: function onPageChange(data) {
          _this3.setState({
            paged: data.selected + 1,
            isfrom: 'pagination'
          });

          _this3.searchFieldPaged.current.value = data.selected;
          setTimeout(function () {
            _this3.searchFormRef.current.querySelector('button[type="submit"]').click();
          }, 50);
        },
        containerClassName: "".concat(alias, "pagination"),
        subContainerClassName: 'pages pagination',
        activeClassName: '_is_selected',
        forcePage: this.state.paged - 1
      }));
    }
  }, {
    key: "buildLoader",
    value: function buildLoader() {
      return /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "loader")
      }, /*#__PURE__*/React.createElement("div", {
        "class": "lds-roller"
      }, /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null), /*#__PURE__*/React.createElement("div", null)));
    }
  }, {
    key: "add_to_log",
    value: function add_to_log() {
      var asin = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      var msg = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
      var type = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'none';
      var logs = this.state.logs; // calculte the total number

      if (type == 'end') {
        msg = "".concat(msg, " <strong>").concat(((new Date() - this.state.logs[asin][0].when) / 1000).toFixed(2), " seconds</strong>");
      }

      var asin_log = Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_isset"])(logs, asin) ? logs[asin].slice(0) : [];
      asin_log.push({
        msg: msg,
        type: type,
        when: new Date()
      }); //this.setState({ logs: { ...this.state.logs, [asin]: asin_log }});

      this.state.logs = _objectSpread({}, this.state.logs, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, asin, asin_log));
    }
  }, {
    key: "unblockWorker",
    value: function unblockWorker() {
      var asin = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      var workers = this.state.workers;
      Object.keys(workers).forEach(function (key) {
        // let's free this worker
        if (workers[key]['asin'] == asin) {
          workers[key].status = 'free';
        }
      });
    }
  }, {
    key: "importWorker",
    value: function importWorker() {
      var _this4 = this;

      window.setInterval(function () {
        var _this4$state = _this4.state,
            import_in_progress = _this4$state.import_in_progress,
            workers = _this4$state.workers,
            products_get_in_progress = _this4$state.products_get_in_progress,
            import_setup = _this4$state.import_setup;
        var self = _this4;

        if (import_in_progress) {
          var _this4$state2 = _this4.state,
              selected_products = _this4$state2.selected_products,
              _import_setup = _this4$state2.import_setup; // check if we have any free workers

          var new_job_key = -1;
          Object.keys(workers).forEach(function (key) {
            var worker = workers[key];

            if (worker.status == 'free') {
              // first ASIN is not already done or in progress
              var new_asin = false;
              Object.keys(selected_products).forEach(function (key) {
                // if product is not already in queue in other worker
                if (!products_get_in_progress.includes(key)) {
                  new_asin = key;
                }
              });

              if (new_asin === false) {
                // stop the import in progress process
                self.setState({
                  import_in_progress: false
                });
                if (self.DEBUG) console.log("no more new products!");
              }

              if (new_asin) {
                // change current free worker status
                workers[key].status = 'busy';
                workers[key].when = new Date();
                workers[key].asin = new_asin; // add to products_get_in_progress queue

                products_get_in_progress.push(new_asin); // it's time to get the product and import

                var page_url = selected_products[new_asin].url;

                if (_import_setup.type == 'simple') {
                  page_url = selected_products[new_asin].variation_url;
                }

                self.add_to_log(new_asin, "===> ".concat(new_asin, " - Start Import"), 'start');
                self.add_to_log(new_asin, "request url: <a href=\"".concat(page_url, "\" target=\"_blank\">").concat(page_url, "</a>"), 'notice'); // make the request to extention for cross domain passing

                window.postMessage({
                  type: "".concat(alias, "transporter"),
                  action: 'url-get-content',
                  params: {
                    'sub_action': 'product_page',
                    'url': page_url,
                    'call_id': new_asin,
                    'delay': Math.floor(Math.random() * 50) + 1,
                    'home_url': WooZoneNoAwsKeysImport.validation.home_url
                  }
                });
                self.setState({
                  products_get_in_progress: products_get_in_progress
                }); // fake get product here ...

                if (0) { var in_time; }
              }
            }
          });
        }
      }, 200);
    }
  }, {
    key: "buildImportList",
    value: function buildImportList() {
      var _this5 = this;

      var _this$state3 = this.state,
          selected_products = _this$state3.selected_products,
          import_setup = _this$state3.import_setup,
          products_get_in_progress = _this$state3.products_get_in_progress,
          import_in_progress = _this$state3.import_in_progress;
      var self = this;
      return Object.keys(selected_products).length ? /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "import-block-wrapper")
      }, /*#__PURE__*/React.createElement("h3", null, "Import list"), /*#__PURE__*/React.createElement("ul", null, Object.keys(selected_products).map(function (key, index) {
        return /*#__PURE__*/React.createElement("li", {
          key: key
        }, /*#__PURE__*/React.createElement("span", {
          "class": "dashicons dashicons-no-alt",
          title: "Remove video from import list",
          onClick: function onClick() {
            if (confirm("Are you sure you want to delete...?")) {
              delete selected_products[key];
              self.setState(selected_products);
            }
          }
        }), /*#__PURE__*/React.createElement("a", {
          href: selected_products[key].url,
          target: "_blank"
        }, /*#__PURE__*/React.createElement("img", {
          src: selected_products[key].image
        })), /*#__PURE__*/React.createElement("h5", null, key), products_get_in_progress.includes(key) ? self.buildLoader() : '');
      })), /*#__PURE__*/React.createElement("form", {
        className: "".concat(alias, "import-form"),
        id: "".concat(alias, "import-form")
      }, /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "setting")
      }, /*#__PURE__*/React.createElement("h3", null, "Import product Type"), /*#__PURE__*/React.createElement("select", {
        className: "".concat(alias, "import-product-type"),
        value: import_setup.type,
        onChange: function onChange(event) {
          return _this5.setState({
            import_setup: _objectSpread({}, import_setup, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, 'type', event.target.value))
          });
        }
      }, /*#__PURE__*/React.createElement("option", {
        value: "simple"
      }, "Simple Product"), /*#__PURE__*/React.createElement("option", {
        value: "variations"
      }, "Variable Product")), import_setup.type == 'simple' ? /*#__PURE__*/React.createElement("p", null, "This option will import only the current product, with no variations.") : /*#__PURE__*/React.createElement("p", null, "This option will import this product parent and its variations according to the settings setup below."), import_setup.type == 'variations' ? /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement("h3", null, "Number of Variations"), /*#__PURE__*/React.createElement(RangeControl, {
        value: import_setup.variations,
        onChange: function onChange(val) {
          return _this5.setState({
            import_setup: _objectSpread({}, import_setup, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, 'variations', val))
          });
        },
        min: 0,
        step: 1,
        max: 99,
        name: "".concat(alias, "field-variations")
      })) : ''), /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "setting")
      }, /*#__PURE__*/React.createElement("h3", null, "Number of Images"), /*#__PURE__*/React.createElement(RangeControl, {
        value: import_setup.images,
        onChange: function onChange(val) {
          return _this5.setState({
            import_setup: _objectSpread({}, import_setup, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, 'images', val))
          });
        },
        min: 0,
        step: 1,
        max: 100,
        name: "".concat(alias, "field-images")
      })), /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "setting")
      }, /*#__PURE__*/React.createElement("h3", null, "Others"), /*#__PURE__*/React.createElement(CheckboxControl, {
        label: "Import attributes",
        checked: import_setup.attributes,
        onChange: function onChange(val) {
          return _this5.setState({
            import_setup: _objectSpread({}, import_setup, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, 'attributes', !import_setup.attributes))
          });
        },
        name: "".concat(alias, "field-attributes")
      }), /*#__PURE__*/React.createElement(CheckboxControl, {
        label: "Spin on Import",
        checked: import_setup.spin,
        onChange: function onChange(val) {
          return _this5.setState({
            import_setup: _objectSpread({}, import_setup, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, 'spin', !import_setup.spin))
          });
        },
        name: "".concat(alias, "field-spin")
      }), /*#__PURE__*/React.createElement(RangeControl, {
        value: import_setup.threads,
        onChange: function onChange(val) {
          return _this5.setState({
            import_setup: _objectSpread({}, import_setup, _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()({}, 'threads', val))
          });
        },
        min: 1,
        label: "Nb. of threads simultaneously (how many products to import in the same time)",
        step: 1,
        max: 5,
        className: "".concat(alias, "field-threads"),
        name: "".concat(alias, "field-threads")
      })), /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "setting")
      }, /*#__PURE__*/React.createElement("h3", null, "Import in"), /*#__PURE__*/React.createElement("div", {
        dangerouslySetInnerHTML: {
          __html: WooZoneNoAwsKeysImport.import_in_category
        }
      })), import_in_progress ? /*#__PURE__*/React.createElement("button", {
        className: "".concat(alias, "stop-btn"),
        onClick: function onClick(e) {
          e.preventDefault();

          _this5.setState({
            import_in_progress: false
          });
        }
      }, "Stop Import") : /*#__PURE__*/React.createElement("button", {
        className: "".concat(alias, "import-btn"),
        onClick: this.startImport.bind(this)
      }, "Import selected products"))) : '';
    }
  }, {
    key: "startImport",
    value: function startImport(event) {
      event.preventDefault(); // create workers based on import setup number of threads

      var workers = {};
      var nb_of_workers = this.state.import_setup.threads; // we don't need more workers than selected products

      if (Object.keys(this.state.selected_products).length < nb_of_workers) {
        nb_of_workers = Object.keys(this.state.selected_products).length;
      } // create workers here


      for (var cc = 0; cc < nb_of_workers; cc++) {
        workers[cc] = {
          status: 'free',
          when: new Date(),
          asin: ''
        };
      }

      this.setState({
        workers: workers,
        import_in_progress: true
      });
    }
  }, {
    key: "buildLogList",
    value: function buildLogList() {
      var _this6 = this;

      var _this$state4 = this.state,
          logs = _this$state4.logs,
          imported_products = _this$state4.imported_products,
          view_log_asin = _this$state4.view_log_asin;

      if (Object.keys(logs).length > 0) {
        if (view_log_asin === false) {
          this.setState({
            view_log_asin: Object.keys(logs)[0]
          });
        }

        return /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "import-log-wrapper ").concat(this.state.show_log ? '' : 'is_hide')
        }, /*#__PURE__*/React.createElement("a", {
          href: "#",
          title: "Click to Close this Panel",
          onClick: function onClick(e) {
            e.preventDefault();

            _this6.setState({
              show_log: !_this6.state.show_log
            });
          },
          className: "".concat(alias, "show-log-btn")
        }, /*#__PURE__*/React.createElement("span", {
          "class": "dashicons dashicons-info"
        }), /*#__PURE__*/React.createElement("span", null, "import log")), /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "import-log-scrollbar")
        }, /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "import-log-anchors-wrapper")
        }, /*#__PURE__*/React.createElement("ul", {
          className: "".concat(alias, "import-log-anchors")
        }, Object.keys(logs).map(function (asin, index) {
          // calculte the total number
          var total_time = 0;
          var last_key = Object.keys(logs[asin])[Object.keys(logs[asin]).length - 1];

          if (logs[asin][last_key].type == 'end') {
            total_time = ((logs[asin][last_key].when - logs[asin][0].when) / 1000).toFixed(2);
          }

          return /*#__PURE__*/React.createElement("li", {
            key: index,
            className: view_log_asin == asin ? "".concat(alias, "asin-selected") : '',
            onClick: function onClick() {
              _this6.setState({
                view_log_asin: asin
              });
            }
          }, total_time > 0 ? /*#__PURE__*/React.createElement("div", {
            className: "".concat(alias, "asin-total-times")
          }, total_time, " sec.") : /*#__PURE__*/React.createElement("div", null, "..."), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", null, asin), !Object.keys(imported_products).includes(asin) ? /*#__PURE__*/React.createElement("span", {
            className: "".concat(alias, "asin-in-progress")
          }, "in progress") : /*#__PURE__*/React.createElement("span", {
            className: "".concat(alias, "asin-").concat(imported_products[asin].status)
          }, imported_products[asin].status)));
        }))), /*#__PURE__*/React.createElement("ul", {
          className: "".concat(alias, "import-log")
        }, Object.keys(logs).map(function (asin, index) {
          if (view_log_asin == asin) {
            return /*#__PURE__*/React.createElement("li", {
              key: index
            }, logs[asin].map(function (log, index) {
              return /*#__PURE__*/React.createElement("p", {
                key: index,
                className: "".concat(alias, "import-status-").concat(log.type)
              }, /*#__PURE__*/React.createElement("span", {
                className: "".concat(alias, "import-time")
              }, "[", "".concat(log.when.getHours(), ":").concat(log.when.getMinutes(), ":").concat(log.when.getSeconds(), ":").concat(log.when.getMilliseconds()), "]"), /*#__PURE__*/React.createElement("span", {
                className: "".concat(alias, "import-msg")
              }, /*#__PURE__*/React.createElement("span", {
                dangerouslySetInnerHTML: {
                  __html: log.msg
                }
              })));
            }));
          }
        }))));
      }
    }
  }, {
    key: "launchSearch",
    value: function launchSearch(event) {
      event.preventDefault();
      var _this$state5 = this.state,
          country = _this$state5.country,
          keyword = _this$state5.keyword,
          categ = _this$state5.categ,
          paged = _this$state5.paged,
          search_sort = _this$state5.search_sort,
          isfrom = _this$state5.isfrom;
      console.log('isfrom', isfrom); // reset results

      this.search_results_products = {};

      if ('pagination' !== isfrom) {
        paged = 1;
        this.setState({
          paged: 1,
          pagination: {}
        });
      }

      var search_url = "https://www.amazon.".concat(country, "/s?k=").concat(keyword, "&i=").concat(categ, "&page=").concat(paged);

      if ('' !== search_sort) {
        search_url = "".concat(search_url, "&s=").concat(search_sort);
      } // debug
      //search_url = '../page2.html';


      window.postMessage({
        type: "".concat(alias, "transporter"),
        action: 'url-get-content',
        params: {
          'sub_action': 'search',
          'url': search_url,
          'call_id': Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneMakeID"])(),
          'delay': 100,
          'home_url': WooZoneNoAwsKeysImport.validation.home_url
        }
      });
      this.setState({
        show_main_loader: true,
        search_result_page: null
      });
    }
  }, {
    key: "launchValidation",
    value: function launchValidation(event) {
      event.preventDefault();
      var ipc = $.trim(event.target.elements.ipc.value);
      var email = event.target.elements.email.value;
      var home_url = event.target.elements.home_url.value;
      var is_valid_ipc = true;

      if (ipc == '') {
        is_valid_ipc = false;
      }

      if (is_valid_ipc == false) {
        alert("invalid IPC code!");
      }

      var validate_url = "http://cc.aa-team.com/validation/validate.php?ipc=".concat(ipc, "&home_url=").concat(home_url, "&email=").concat(email, "&app=").concat(WooZoneNoAwsKeysImport.validation.plugin_alias, "-extension"); // debug
      //search_url = '../page2.html';

      window.postMessage({
        type: "".concat(alias, "transporter"),
        action: 'url-get-content',
        params: {
          'sub_action': 'validation',
          'url': validate_url + "&no_cache=".concat(Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneMakeID"])()),
          'call_id': Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneMakeID"])(),
          'delay': 1,
          'home_url': WooZoneNoAwsKeysImport.validation.home_url
        }
      });
      this.setState({
        show_main_loader: true
      });
    }
  }, {
    key: "removeAllFromQueue",
    value: function removeAllFromQueue(event) {
      event.preventDefault();

      if (confirm("Are you sure you want to remove all the products from import Queue?")) {
        this.setState({
          selected_products: {}
        });
      }
    }
  }, {
    key: "addAllToQueue",
    value: function addAllToQueue(event) {
      var _this7 = this;

      event.preventDefault();
      var new_products = {};
      Object.keys(this.search_results_products).forEach(function (key) {
        var product = _this7.search_results_products[key];

        if (!product.already_imported) {
          new_products[key] = product;
        }
      });
      this.setState({
        selected_products: _objectSpread({}, this.state.selected_products, {}, new_products)
      });
    }
  }, {
    key: "buildQuickMenu",
    value: function buildQuickMenu() {
      var _this8 = this;

      var _this$state6 = this.state,
          quick_menu = _this$state6.quick_menu,
          selected_products = _this$state6.selected_products;
      return /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "quick-menu ").concat(quick_menu == 'open' ? '__is_opened' : '')
      }, /*#__PURE__*/React.createElement("h2", {
        onClick: function onClick() {
          return _this8.setState({
            quick_menu: quick_menu == 'open' ? 'close' : 'open'
          });
        }
      }, quick_menu == 'open' ? /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement("span", {
        "class": "dashicons dashicons-arrow-down-alt2"
      }), "Close Quick Menu") : /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement("span", {
        "class": "dashicons dashicons-arrow-up-alt2"
      }), "Open Quick Menu")), /*#__PURE__*/React.createElement("p", null, "This menu is contextual, you can find more or less actions according to your search."), /*#__PURE__*/React.createElement("ul", null, this.search_results_products && Object.keys(this.search_results_products).length ? /*#__PURE__*/React.createElement("li", null, /*#__PURE__*/React.createElement("a", {
        href: "#",
        onClick: this.addAllToQueue.bind(this)
      }, "Add All NEW Products to Queue")) : '', Object.keys(selected_products).length ? /*#__PURE__*/React.createElement("li", null, /*#__PURE__*/React.createElement("a", {
        href: "#",
        onClick: this.removeAllFromQueue.bind(this)
      }, "Empty Products Queue")) : '', /*#__PURE__*/React.createElement("li", null, /*#__PURE__*/React.createElement("a", {
        href: "#",
        onClick: function onClick(event) {
          event.preventDefault();
          window.scrollTo(0, 0);
        }
      }, /*#__PURE__*/React.createElement("span", {
        "class": "dashicons dashicons-arrow-up-alt"
      }), " Scroll to TOP")), /*#__PURE__*/React.createElement("li", null, /*#__PURE__*/React.createElement("a", {
        href: "#",
        onClick: function onClick(event) {
          event.preventDefault();
          window.scrollTo(0, document.body.scrollHeight);
        }
      }, /*#__PURE__*/React.createElement("span", {
        "class": "dashicons dashicons-arrow-down-alt"
      }), " Scroll to BOTTOM"))));
    }
  }, {
    key: "render",
    value: function render() {
      var _this9 = this;

      var _this$state7 = this.state,
          country = _this$state7.country,
          keyword = _this$state7.keyword,
          search_result_page = _this$state7.search_result_page,
          paged = _this$state7.paged,
          show_main_loader = _this$state7.show_main_loader,
          import_setup = _this$state7.import_setup,
          products = _this$state7.products,
          imported_products = _this$state7.imported_products,
          is_chrome = _this$state7.is_chrome,
          extention_loaded = _this$state7.extention_loaded,
          ipc_is_valid = _this$state7.ipc_is_valid;

      if (is_chrome === false) {
        return /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "get-google-chrome")
        }, /*#__PURE__*/React.createElement("img", {
          src: "".concat(WooZoneNoAwsKeysImport.assets_url, "images/chrome-logo.svg")
        }), /*#__PURE__*/React.createElement("h1", null, "This module works only in Google Chrome Browser and using ", /*#__PURE__*/React.createElement("a", {
          href: "https://chrome.google.com/webstore/detail/wzone-direct-import/gmpiiinlandbgcfejoeaodgpfkdjnolm?hl=en",
          target: "_blank"
        }, "WZone Direct Import"), "."), /*#__PURE__*/React.createElement("a", {
          className: "_is_btn",
          href: "https://www.google.com/chrome/",
          target: "_blank"
        }, "Download Chrome"));
      }

      if (extention_loaded === false) {
        return /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "get-extension")
        }, /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "validate-left")
        }, /*#__PURE__*/React.createElement("p", null, "Please check if the WZone Direct Import extension is installed!"), /*#__PURE__*/React.createElement("p", {
          className: "".concat(alias, "info-block")
        }, "In order to use this module please make sure you have the latest WZone Direct Import version installed - V2.0 "), /*#__PURE__*/React.createElement("p", {
          className: "".concat(alias, "info-text")
        }, "After you install/update it please refresh this page."), /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "get-extension-block")
        }, /*#__PURE__*/React.createElement("img", {
          src: "".concat(WooZoneNoAwsKeysImport.assets_url, "images/direct-import-logo.png")
        }), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h4", null, "WZone Direct Import"), /*#__PURE__*/React.createElement("a", {
          className: "_is_btn",
          href: "https://chrome.google.com/webstore/detail/wzone-direct-import/gmpiiinlandbgcfejoeaodgpfkdjnolm?hl=en",
          target: "_blank"
        }, "Add to Chrome")))), /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "validate-right")
        }, /*#__PURE__*/React.createElement("img", {
          src: "".concat(WooZoneNoAwsKeysImport.assets_url, "images/API-NO.jpg")
        })));
      }

      if (ipc_is_valid === false) {
        return /*#__PURE__*/React.createElement(Fragment, null, show_main_loader ? this.buildLoader() : '', /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "validate-extension")
        }, /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "validate-left")
        }, /*#__PURE__*/React.createElement("h2", null, "WZone Direct Import Extension Validation."), /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "validate-extension-info")
        }, /*#__PURE__*/React.createElement("p", null, "This code is used for validation so you can use the NO AWS KEYS Import Feature."), /*#__PURE__*/React.createElement("p", null, "You need to log into your CodeCanyon account and go to your \u201CDownloads\u201D page. Locate the plugin you purchased in your \u201CDownloads\u201D list and click on the \u201CLicense Certificate\u201D link next to the download link. After you have downloaded the certificate you can open it in a text editor such as Notepad and copy the Item Purchase Code."), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("a", {
          target: "_blank",
          href: "http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/activate-plugin/"
        }, "Read more here. "))), /*#__PURE__*/React.createElement("form", {
          onSubmit: this.launchValidation.bind(this)
        }, /*#__PURE__*/React.createElement("input", {
          type: "hidden",
          name: "home_url",
          value: WooZoneNoAwsKeysImport.validation.home_url
        }), /*#__PURE__*/React.createElement("input", {
          type: "text",
          name: "ipc",
          defaultValue: WooZoneNoAwsKeysImport.validation.ipc ? WooZoneNoAwsKeysImport.validation.ipc : '',
          placeholder: "Enter your Envato Purchase Code here"
        }), /*#__PURE__*/React.createElement("input", {
          type: "text",
          name: "email",
          defaultValue: WooZoneNoAwsKeysImport.validation.email ? WooZoneNoAwsKeysImport.validation.email : '',
          placeholder: "Enter site owner email here"
        }), /*#__PURE__*/React.createElement("button", {
          className: "_is_btn"
        }, /*#__PURE__*/React.createElement("span", {
          "class": "dashicons dashicons-admin-network"
        }), " Unlock NO AWS KEYS Import"))), /*#__PURE__*/React.createElement("div", {
          className: "".concat(alias, "validate-right")
        }, /*#__PURE__*/React.createElement("img", {
          src: "".concat(WooZoneNoAwsKeysImport.assets_url, "images/API-NO.jpg")
        }))));
      }

      return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement("form", {
        onSubmit: this.launchSearch.bind(this),
        className: "".concat(alias, "search-form"),
        ref: this.searchFormRef,
        "data-country": country,
        id: "WooZone-NAWS-search-form"
      }, /*#__PURE__*/React.createElement(_country_dropdown_class_js__WEBPACK_IMPORTED_MODULE_7__["AZON_Country_Dropdown"], {
        onChange: function onChange(value) {
          _this9.setState({
            country: value,
            categ: 'aps',
            isfrom: ''
          });
        },
        defaultValue: country,
        list: Object(_amz_utils_js__WEBPACK_IMPORTED_MODULE_8__["WooZoneNoAWS_Country_List"])()
      }), /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "custom-select")
      }, this.buildCateg()), /*#__PURE__*/React.createElement("input", {
        id: "".concat(alias, "filed-keyword"),
        placeholder: "Search keyword here",
        type: "text",
        defaultValue: keyword,
        onChange: function onChange(e) {
          _this9.setState({
            keyword: e.target.value,
            isfrom: ''
          });
        }
      }), /*#__PURE__*/React.createElement("input", {
        id: "".concat(alias, "filed-paged"),
        ref: this.searchFieldPaged,
        type: "hidden",
        defaultValue: paged
      }), /*#__PURE__*/React.createElement("div", {
        className: "".concat(alias, "custom-select")
      }, /*#__PURE__*/React.createElement("select", {
        id: "".concat(alias, "filed-filter"),
        defaultValue: this.state.search_sort,
        onChange: function onChange(e) {
          _this9.setState({
            search_sort: e.target.value,
            isfrom: ''
          });
        }
      }, /*#__PURE__*/React.createElement("option", {
        value: ""
      }, "NONE"), /*#__PURE__*/React.createElement("option", {
        value: "relevanceblender"
      }, "Featured"), /*#__PURE__*/React.createElement("option", {
        value: "price-asc-rank"
      }, "Price: Low to High"), /*#__PURE__*/React.createElement("option", {
        value: "price-desc-rank"
      }, "Price: High to Low"), /*#__PURE__*/React.createElement("option", {
        value: "review-rank"
      }, "Avg. Customer Review"), /*#__PURE__*/React.createElement("option", {
        value: "date-desc-rank"
      }, "Newest Arrivals"))), /*#__PURE__*/React.createElement("button", {
        "class": "wz-search-btn",
        type: "submit"
      }, "Search for Products")), this.buildSearchResults(), show_main_loader ? this.buildLoader() : '', this.buildImportList(), this.buildLogList(), this.buildQuickMenu());
    }
  }]);

  return WooZoneNoAWSImport;
}(Component);

wp.domReady(function () {
  if (image_block_wrapper) render( /*#__PURE__*/React.createElement(WooZoneNoAWSImport, null), image_block_wrapper);
});

/***/ }),

/***/ "./src/app.scss":
/*!**********************!*\
  !*** ./src/app.scss ***!
  \**********************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./src/country-dropdown.class.js":
/*!***************************************!*\
  !*** ./src/country-dropdown.class.js ***!
  \***************************************/
/*! exports provided: AZON_Country_Dropdown */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "AZON_Country_Dropdown", function() { return AZON_Country_Dropdown; });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/createClass.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/inherits.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__);






function _createSuper(Derived) { return function () { var Super = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default()(Derived), result; if (_isNativeReflectConstruct()) { var NewTarget = _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3___default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2___default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

var Component = wp.element.Component;
var alias = 'AZON-country-list';

var AZON_Country_Dropdown = /*#__PURE__*/function (_Component) {
  _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4___default()(AZON_Country_Dropdown, _Component);

  var _super = _createSuper(AZON_Country_Dropdown);

  function AZON_Country_Dropdown(props) {
    var _this;

    _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0___default()(this, AZON_Country_Dropdown);

    _this = _super.call(this, props);
    _this.state = {
      dropdown_state: false,
      current_value: false
    };
    return _this;
  }

  _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1___default()(AZON_Country_Dropdown, [{
    key: "buildListHtml",
    value: function buildListHtml() {
      var self = this;
      var list = this.props.list;
      var dropdown_state = this.state.dropdown_state;
      var list_html = list.map(function (__list, value) {
        return /*#__PURE__*/React.createElement("li", {
          onClick: self.setValue.bind(self, __list),
          key: __list.alias
        }, /*#__PURE__*/React.createElement("img", {
          src: window.WooZoneNoAwsKeysImport.assets_url + __list.flag
        }), /*#__PURE__*/React.createElement("span", null, __list.label));
      });
      return /*#__PURE__*/React.createElement("ul", {
        style: {
          display: dropdown_state ? "block" : "none"
        }
      }, list_html);
    }
  }, {
    key: "printDefaultValue",
    value: function printDefaultValue() {
      var self = this;
      var _this$props = this.props,
          list = _this$props.list,
          defaultValue = _this$props.defaultValue;
      var current_value = this.state.current_value;

      if (current_value != false && current_value != defaultValue) {
        defaultValue = current_value;
      }

      return list.map(function (__list) {
        if (__list.alias == defaultValue) {
          return /*#__PURE__*/React.createElement("span", {
            onClick: self.toggleDropdown.bind(self),
            key: __list.alias,
            className: alias + "-default"
          }, /*#__PURE__*/React.createElement("img", {
            src: window.WooZoneNoAwsKeysImport.assets_url + __list.flag
          }), /*#__PURE__*/React.createElement("span", null, __list.label));
        }
      });
    }
  }, {
    key: "setValue",
    value: function setValue(value) {
      this.setState({
        current_value: value.alias,
        dropdown_state: false
      }); // update parent props

      this.props.onChange(value.alias);
    }
  }, {
    key: "toggleDropdown",
    value: function toggleDropdown() {
      this.setState({
        dropdown_state: !this.state.dropdown_state
      });
    }
  }, {
    key: "render",
    value: function render() {
      var list = this.props.list;

      if (!list) {
        return /*#__PURE__*/React.createElement("p", null, "No list provided!");
      }

      return /*#__PURE__*/React.createElement("div", {
        className: alias + "-wrapper"
      }, this.printDefaultValue(), this.buildListHtml());
    }
  }]);

  return AZON_Country_Dropdown;
}(Component);



/***/ }),

/***/ 0:
/*!******************************************!*\
  !*** multi ./src/app.jsx ./src/app.scss ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /mnt/ssd/www/wp-plugins/WooZoneV9/react-sources/noaws_import/src/app.jsx */"./src/app.jsx");
module.exports = __webpack_require__(/*! /mnt/ssd/www/wp-plugins/WooZoneV9/react-sources/noaws_import/src/app.scss */"./src/app.scss");


/***/ })

/******/ });
//# sourceMappingURL=app.build.noaws_import.js.map