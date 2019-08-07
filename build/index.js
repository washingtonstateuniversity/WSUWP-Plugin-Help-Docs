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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/_js/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/_js/blocks/howto/block.json":
/*!*****************************************!*\
  !*** ./src/_js/blocks/howto/block.json ***!
  \*****************************************/
/*! exports provided: name, category, attributes, default */
/***/ (function(module) {

module.exports = {"name":"hrs-help-docs/howto","category":"common","attributes":{"values":{"type":"string","source":"html","selector":"ol","multiline":"li","default":""}}};

/***/ }),

/***/ "./src/_js/blocks/howto/edit.js":
/*!**************************************!*\
  !*** ./src/_js/blocks/howto/edit.js ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return HowToEdit; });
!(function webpackMissingModule() { var e = new Error("Cannot find module '@wordpress/element'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());


/**
 * WordPress dependencies
 */
var __ = wp.i18n.__;
var createBlock = wp.blocks.createBlock;
var RichText = wp.editor.RichText;
function HowToEdit(_ref) {
  var attributes = _ref.attributes,
      insertBlocksAfter = _ref.insertBlocksAfter,
      setAttributes = _ref.setAttributes,
      mergeBlocks = _ref.mergeBlocks,
      onReplace = _ref.onReplace,
      className = _ref.className;
  var values = attributes.values;
  return !(function webpackMissingModule() { var e = new Error("Cannot find module '@wordpress/element'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(RichText, {
    identifier: "values",
    multiline: "li",
    tagName: "ol",
    onChange: function onChange(nextValues) {
      return setAttributes({
        values: nextValues
      });
    },
    value: values,
    wrapperClassName: "howto-list",
    className: className,
    placeholder: __('Write instructions…'),
    onMerge: mergeBlocks,
    unstableOnSplit: insertBlocksAfter ? function (before, after) {
      for (var _len = arguments.length, blocks = new Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
        blocks[_key - 2] = arguments[_key];
      }

      if (!blocks.length) {
        blocks.push(createBlock('core/paragraph'));
      }

      if (after !== '<li></li>') {
        blocks.push(createBlock('core/list', {
          values: after
        }));
      }

      setAttributes({
        values: before
      });
      insertBlocksAfter(blocks);
    } : undefined,
    onRemove: function onRemove() {
      return onReplace([]);
    }
  });
}

/***/ }),

/***/ "./src/_js/blocks/howto/index.js":
/*!***************************************!*\
  !*** ./src/_js/blocks/howto/index.js ***!
  \***************************************/
/*! exports provided: metadata, name, settings */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "name", function() { return name; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "settings", function() { return settings; });
!(function webpackMissingModule() { var e = new Error("Cannot find module '@wordpress/element'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./edit */ "./src/_js/blocks/howto/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./block.json */ "./src/_js/blocks/howto/block.json");
var _block_json__WEBPACK_IMPORTED_MODULE_2___namespace = /*#__PURE__*/__webpack_require__.t(/*! ./block.json */ "./src/_js/blocks/howto/block.json", 1);
/* harmony reexport (default from named exports) */ __webpack_require__.d(__webpack_exports__, "metadata", function() { return _block_json__WEBPACK_IMPORTED_MODULE_2__; });
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./src/_js/blocks/howto/save.js");


/**
 * WordPress dependencies
 */
var __ = wp.i18n.__;
/**
 * Internal dependencies
 */




var name = _block_json__WEBPACK_IMPORTED_MODULE_2__.name;

var supports = {
  align: ['wide', 'full']
};
var settings = {
  title: __('How To'),
  description: __('Display an instructional list.'),
  icon: !(function webpackMissingModule() { var e = new Error("Cannot find module '@wordpress/element'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "468 268 24 24"
  }, !(function webpackMissingModule() { var e = new Error("Cannot find module '@wordpress/element'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())("path", {
    fill: "none",
    d: "M468 268h24v24h-24v-24z"
  }), !(function webpackMissingModule() { var e = new Error("Cannot find module '@wordpress/element'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())("path", {
    d: "M472 272h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-16a2 2 0 0 1-2-2v-12c0-1.1.9-2 2-2zm0 2v12h10v-12h-10zm12 0v12h4v-12h-4z"
  })),
  keywords: [__('ordered list'), __('instructions'), __('numbered list')],
  supports: supports,
  edit: _edit__WEBPACK_IMPORTED_MODULE_1__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_3__["default"]
};

/***/ }),

/***/ "./src/_js/blocks/howto/save.js":
/*!**************************************!*\
  !*** ./src/_js/blocks/howto/save.js ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return save; });
!(function webpackMissingModule() { var e = new Error("Cannot find module '@wordpress/element'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());


/**
 * WordPress dependencies
 */
var RichText = wp.editor.RichText;
function save(_ref) {
  var attributes = _ref.attributes;
  var values = attributes.values;
  var tagName = 'ol';
  return !(function webpackMissingModule() { var e = new Error("Cannot find module '@wordpress/element'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(RichText.Content, {
    tagName: tagName,
    value: values,
    multiline: "li"
  });
}

/***/ }),

/***/ "./src/_js/blocks/index.js":
/*!*********************************!*\
  !*** ./src/_js/blocks/index.js ***!
  \*********************************/
/*! exports provided: registerHelpDocBlocks */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "registerHelpDocBlocks", function() { return registerHelpDocBlocks; });
/* harmony import */ var _howto__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./howto */ "./src/_js/blocks/howto/index.js");
/**
 * WordPress dependencies
 */
var registerBlockType = wp.blocks.registerBlockType;
/**
 * Internal dependencies
 */


/**
 * Function to register WSUWP Help Docs blocks.
 *
 * @example
 * ```js
 * import { registerHelpDocBlocks } from './blocks';
 *
 * registerHelpDocBlocks();
 * ```
 */

var registerHelpDocBlocks = function registerHelpDocBlocks() {
  [_howto__WEBPACK_IMPORTED_MODULE_0__].forEach(function (block) {
    if (!block) {
      return;
    }

    var settings = block.settings,
        name = block.name;
    registerBlockType(name, settings);
  });
};

/***/ }),

/***/ "./src/_js/index.js":
/*!**************************!*\
  !*** ./src/_js/index.js ***!
  \**************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./blocks */ "./src/_js/blocks/index.js");
/**
 * Internal dependencies
 */
//import { init as modifyBlockStyles } from './blocks/styles';

Object(_blocks__WEBPACK_IMPORTED_MODULE_0__["registerHelpDocBlocks"])();

/***/ })

/******/ });
//# sourceMappingURL=index.js.map