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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
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
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(1);
module.exports = __webpack_require__(2);


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var _wp$element = wp.element,
    Component = _wp$element.Component,
    render = _wp$element.render,
    findDOMNode = _wp$element.findDOMNode,
    Fragment = _wp$element.Fragment;


var $ = jQuery;
var _wp$components = wp.components,
    RangeControl = _wp$components.RangeControl,
    CheckboxControl = _wp$components.CheckboxControl;

//import ReactPaginate from 'react-paginate';
//import md5 from 'js-md5';

/*import { AZON_Country_Dropdown } from './country-dropdown.class.js';
import {
  WooZoneNoAWSCategs as AzonCategs,
  WooZoneNoAWS_Country_List as Country_List,
  WooZoneNoAWS_isset as isset,
  WooZoneParser,
  WooZoneMakeID as makeid,
  WooZoneChunk as chunk,
  WooZoneHumanFileSize as humanFileSize,
  WooZoneGetDomain as getDomain,
  WooZoneContentTrimmer as contentTrimmer
} from "./amz.utils.js";*/

var alias = 'WooZone-SyncWidget-';
var image_block_wrapper = document.getElementById(alias + 'wrapper');

var WooZoneNoAWS_SyncWidget = function (_Component) {
  _inherits(WooZoneNoAWS_SyncWidget, _Component);

  function WooZoneNoAWS_SyncWidget(props) {
    _classCallCheck(this, WooZoneNoAWS_SyncWidget);

    var _this = _possibleConstructorReturn(this, (WooZoneNoAWS_SyncWidget.__proto__ || Object.getPrototypeOf(WooZoneNoAWS_SyncWidget)).call(this, props));

    _this.DEBUG = true;

    _this.state = {
      show_log: true
    };
    return _this;
  }

  _createClass(WooZoneNoAWS_SyncWidget, [{
    key: 'componentDidMount',
    value: function componentDidMount() {
      var self = this;
    }
  }, {
    key: 'render',
    value: function render() {
      var show_log = this.state.show_log;


      return React.createElement(
        Fragment,
        null,
        React.createElement(
          'div',
          null,
          'gimi is here'
        )
      );
    }
  }]);

  return WooZoneNoAWS_SyncWidget;
}(Component);

wp.domReady(function () {
  if (image_block_wrapper) render(React.createElement(WooZoneNoAWS_SyncWidget, null), image_block_wrapper);
});

/***/ }),
/* 2 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })
/******/ ]);
//# sourceMappingURL=app.build.js.map