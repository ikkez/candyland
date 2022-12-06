// IE11 polyfill
if (!Object.values)
	Object.values = function(obj){
		return Object.keys(obj).map(function(e) {
			return obj[e]
		});
	};

function createEvent(e, bubbles, cancelable, detail) {
	if ( bubbles === void 0 ) bubbles = true;
	if ( cancelable === void 0 ) cancelable = false;

	if (typeof e === 'string') {
		var event = document.createEvent('CustomEvent'); // IE 11
		event.initCustomEvent(e, bubbles, cancelable, detail);
		e = event;
	}
	return e;
}

function getParameterByName(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, '\\$&');
	var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

var basePath;

window.addEventListener('load', function() {

	var frameConfig = function () {
		console.log('frameconfig');
	};

	var pageId = getParameterByName('ct_editor');

	basePath = document.getElementsByTagName('base')[0].getAttribute('href');

	var lang = document.documentElement.lang;

	// var MockAPI, MockRequest,
	// 	__hasProp = {}.hasOwnProperty,
	// 	__extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };
	//
	// MockRequest = (function() {
	// 	function MockRequest(responseText) {
	// 		var mockLoad;
	// 		this._responseText = responseText;
	// 		this._listener = null;
	// 		mockLoad = (function(_this) {
	// 			return function() {
	// 				if (_this._listener) {
	// 					return _this._listener({
	// 						target: {
	// 							responseText: responseText
	// 						}
	// 					});
	// 				}
	// 			};
	// 		})(this);
	// 		setTimeout(mockLoad, 0);
	// 	}
	//
	// 	MockRequest.prototype.addEventListener = function(eventType, listener) {
	// 		return this._listener = listener;
	// 	};
	//
	// 	return MockRequest;
	//
	// })();

	// MockAPI = (function(_super) {
	// 	__extends(MockAPI, _super);
	//
	// 	MockAPI._autoInc = 0;
	//
	// 	function MockAPI(baseURL, baseParams) {
	// 		if (baseURL == null) {
	// 			baseURL = '/';
	// 		}
	// 		if (baseParams == null) {
	// 			baseParams = {};
	// 		}
	// 		MockAPI.__super__.constructor.call(this, baseURL = '/', baseParams = {});
	// 		this._snippetTypes = {
	// 			'article-body': [
	// 				{
	// 					'id': 'basic',
	// 					'label': 'Standard-Inhalt'
	// 				}, {
	// 					'id': 'advanced',
	// 					'label': 'Accordeon'
	// 				}
	// 			],
	// 			// 'article-related': [
	// 			// 	{
	// 			// 		'id': 'basic',
	// 			// 		'label': 'Basic'
	// 			// 	}, {
	// 			// 		'id': 'archive',
	// 			// 		'label': 'Archive'
	// 			// 	}
	// 			// ]
	// 		};
	// 		this._snippets = {
	// 			'article-body': [
	// 				// {
	// 				// 	'id': this.constructor._getId(),
	// 				// 	'type': this._snippetTypes['article-body'][0],
	// 				// 	'scope': 'local',
	// 				// 	'settings': {}
	// 				// }, {
	// 				// 	'id': this.constructor._getId(),
	// 				// 	'type': this._snippetTypes['article-body'][1],
	// 				// 	'scope': 'local',
	// 				// 	'settings': {}
	// 				// }
	// 			],
	// 			// 'article-related': [
	// 			// 	{
	// 			// 		'id': this.constructor._getId(),
	// 			// 		'type': this._snippetTypes['article-related'][1],
	// 			// 		'scope': 'local',
	// 			// 		'settings': {}
	// 			// 	}, {
	// 			// 		'id': this.constructor._getId(),
	// 			// 		'type': this._snippetTypes['article-related'][0],
	// 			// 		'scope': 'local',
	// 			// 		'settings': {}
	// 			// 	}
	// 			// ]
	// 		};
	// 		this._globalSnippets = {
	// 			'article-body': [
	// 		// 		{
	// 		// 			'id': this.constructor._getId(),
	// 		// 			'type': this._snippetTypes['article-body'][0],
	// 		// 			'scope': 'global',
	// 		// 			'settings': {},
	// 		// 			'global_id': this.constructor._getId(),
	// 		// 			'label': 'Client logos'
	// 		// 		}
	// 			],
	// 		// 	'article-related': []
	// 		};
	// 	}
	//
	// 	MockAPI._getId = function() {
	// 		this._autoInc += 1;
	// 		return this._autoInc;
	// 	};
	//
	// 	MockAPI.prototype._callEndpoint = function(method, endpoint, params) {
	// 		console.log(method,endpoint,params);
	// 		var fields, globalId, globalSnippet, id, newSnippets, otherSnippet, snippet, snippetType, snippets, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _len5, _m, _n, _ref, _ref1, _ref2, _ref3, _ref4;
	// 		if (params == null) {
	// 			params = {};
	// 		}
	// 		switch (endpoint) {
	// 			case 'add-snippet':
	// 				snippetType = null;
	// 				_ref = this._snippetTypes[params['flow']];
	// 				for (_i = 0, _len = _ref.length; _i < _len; _i++) {
	// 					snippetType = _ref[_i];
	// 					if (snippetType.id === params['snippet_type']) {
	// 						break;
	// 					}
	// 				}
	// 				snippet = {
	// 					'id': this.constructor._getId(),
	// 					'type': snippetType,
	// 					'scope': 'local',
	// 					'settings': {}
	// 				};
	// 				this._snippets[params['flow']].push(snippet);
	// 				return this._mockResponse({
	// 					'html': "<div class=\"content-snippet\" data-cf-snippet=\"" + snippet.id + "\">\n    <p>This is a new snippet</p>\n    <section\n        data-cf-flow=\"new\"\n        data-cf-flow-label=\"New\"\n        >\n    </section>\n</div>"
	// 				});
	// 			case 'add-global-snippet':
	// 				globalSnippet = null;
	// 				_ref1 = this._globalSnippets[params['flow']];
	// 				for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
	// 					globalSnippet = _ref1[_j];
	// 					if (globalSnippet.global_id === params['global_snippet']) {
	// 						break;
	// 					}
	// 				}
	// 				snippet = {
	// 					'id': this.constructor._getId(),
	// 					'type': globalSnippet.type,
	// 					'scope': globalSnippet.scope,
	// 					'settings': globalSnippet.settings,
	// 					'global_id': globalSnippet.id,
	// 					'label': globalSnippet.label
	// 				};
	// 				this._snippets[params['flow']].push(snippet);
	// 				return this._mockResponse({
	// 					'html': "<div class=\"content-snippet\" data-cf-snippet=\"" + snippet.id + "\">\n    <p>This is a global snippet: " + snippet.label + "</p>\n</div>"
	// 				});
	// 			case 'delete-snippet':
	// 				snippets = this._snippets[params['flow']];
	// 				newSnippets = [];
	// 				for (_k = 0, _len2 = snippets.length; _k < _len2; _k++) {
	// 					snippet = snippets[_k];
	// 					if (snippet.id !== params['snippet']) {
	// 						newSnippets.push(snippet);
	// 					}
	// 				}
	// 				this._snippets[params['flow']] = newSnippets;
	// 				return this._mockResponse();
	// 			case 'global-snippets':
	// 				return this._mockResponse({
	// 					'snippets': this._globalSnippets[params['flow']]
	// 				});
	// 			case 'order-snippets':
	// 				snippets = {};
	// 				_ref2 = this._snippets[params['flow']];
	// 				for (_l = 0, _len3 = _ref2.length; _l < _len3; _l++) {
	// 					snippet = _ref2[_l];
	// 					snippets[snippet.id] = snippet;
	// 				}
	// 				newSnippets = [];
	// 				_ref3 = params['snippets'];
	// 				for (_m = 0, _len4 = _ref3.length; _m < _len4; _m++) {
	// 					id = _ref3[_m];
	// 					newSnippets.push(snippets[id]);
	// 				}
	// 				this._snippets[params['flow']] = newSnippets;
	// 				return this._mockResponse();
	// 			case 'snippets':
	// 				return this._mockResponse({
	// 					'snippets': this._snippets[params['flow']]
	// 				});
	// 			case 'change-snippet-scope':
	// 				snippet = null;
	// 				_ref4 = this._snippets[params['flow']];
	// 				for (_n = 0, _len5 = _ref4.length; _n < _len5; _n++) {
	// 					otherSnippet = _ref4[_n];
	// 					if (otherSnippet.id === params['snippet']) {
	// 						snippet = otherSnippet;
	// 						break;
	// 					}
	// 				}
	// 				if (params['scope'] === 'local') {
	// 					snippet.scope = 'local';
	// 					delete snippet.global_id;
	// 					delete snippet.label;
	// 					return this._mockResponse();
	// 				} else {
	// 					if (!params['label']) {
	// 						return this._mockError({
	// 							'label': 'This field is required'
	// 						});
	// 					}
	// 					globalId = this.constructor._getId();
	// 					this._globalSnippets[params['flow']].push({
	// 						'id': this.constructor._getId(),
	// 						'type': snippet.type,
	// 						'scope': 'global',
	// 						'settings': snippet.settigns,
	// 						'global_id': globalId,
	// 						'label': params['label']
	// 					});
	// 					snippet.scope = 'global';
	// 					snippet.global_id = globalId;
	// 					snippet.label = params['label'];
	// 					return this._mockResponse();
	// 				}
	// 				break;
	// 			case 'update-snippet-settings':
	// 				if (method.toLowerCase() === 'get') {
	// 					fields = [
	// 						{
	// 							'type': 'boolean',
	// 							'name': 'boolean_example',
	// 							'label': 'Boolean example',
	// 							'required': false,
	// 							'value': true
	// 						}, {
	// 							'type': 'select',
	// 							'name': 'select_example',
	// 							'label': 'Select example',
	// 							'required': true,
	// 							'value': 1,
	// 							'choices': [[1, 'One'], [2, 'Two'], [3, 'Three']]
	// 						}, {
	// 							'type': 'text',
	// 							'name': 'Text_example',
	// 							'label': 'Text example',
	// 							'required': true,
	// 							'value': 'foo'
	// 						}
	// 					];
	// 					return this._mockResponse({
	// 						'fields': fields
	// 					});
	// 				} else {
	//
	// 				}
	// 				return this._mockResponse({
	// 					'html': "<div class=\"content-snippet\" data-cf-snippet=\"" + params['snippet'] + "\">\n    <p>This is a snippet with updated settings</p>\n</div>"
	// 				});
	// 			case 'snippet-types':
	// 				return this._mockResponse({
	// 					'snippet_types': this._snippetTypes[params['flow']]
	// 				});
	// 		}
	// 	};
	//
	// 	MockAPI.prototype._mockError = function(errors) {
	// 		var response;
	// 		response = {
	// 			'status': 'fail'
	// 		};
	// 		if (errors) {
	// 			response['errors'] = errors;
	// 		}
	// 		return new MockRequest(JSON.stringify(response));
	// 	};
	//
	// 	MockAPI.prototype._mockResponse = function(payload) {
	// 		var response;
	// 		response = {
	// 			'status': 'success'
	// 		};
	// 		if (payload) {
	// 			response['payload'] = payload;
	// 		}
	// 		return new MockRequest(JSON.stringify(response));
	// 	};
	//
	// 	return MockAPI;
	//
	// })(ContentFlow.BaseAPI);



	// var ImageUploader;
	//
	// ImageUploader = (function () {
	// 	ImageUploader.imagePath = 'upload';
	//
	// 	ImageUploader.imageSize = [600, 174];
	//
	// 	function ImageUploader(dialog) {
	// 		this._dialog = dialog;
	// 		this._dialog.addEventListener('cancel', (function (_this) {
	// 			return function () {
	// 				return _this._onCancel();
	// 			};
	// 		})(this));
	// 		this._dialog.addEventListener('imageuploader.cancelupload', (function (_this) {
	// 			return function () {
	// 				return _this._onCancelUpload();
	// 			};
	// 		})(this));
	// 		this._dialog.addEventListener('imageuploader.clear', (function (_this) {
	// 			return function () {
	// 				return _this._onClear();
	// 			};
	// 		})(this));
	// 		this._dialog.addEventListener('imageuploader.fileready', (function (_this) {
	// 			return function (ev) {
	// 				return _this._onFileReady(ev.detail().file);
	// 			};
	// 		})(this));
	// 		this._dialog.addEventListener('imageuploader.mount', (function (_this) {
	// 			return function () {
	// 				return _this._onMount();
	// 			};
	// 		})(this));
	// 		this._dialog.addEventListener('imageuploader.rotateccw', (function (_this) {
	// 			return function () {
	// 				return _this._onRotateCCW();
	// 			};
	// 		})(this));
	// 		this._dialog.addEventListener('imageuploader.rotatecw', (function (_this) {
	// 			return function () {
	// 				return _this._onRotateCW();
	// 			};
	// 		})(this));
	// 		this._dialog.addEventListener('imageuploader.save', (function (_this) {
	// 			return function () {
	// 				return _this._onSave();
	// 			};
	// 		})(this));
	// 		this._dialog.addEventListener('imageuploader.unmount', (function (_this) {
	// 			return function () {
	// 				return _this._onUnmount();
	// 			};
	// 		})(this));
	// 	}
	//
	// 	ImageUploader.prototype._onCancel = function () {
	// 	};
	//
	// 	ImageUploader.prototype._onCancelUpload = function () {
	// 		clearTimeout(this._uploadingTimeout);
	// 		return this._dialog.state('empty');
	// 	};
	//
	// 	ImageUploader.prototype._onClear = function () {
	// 		return this._dialog.clear();
	// 	};
	//
	// 	ImageUploader.prototype._onFileReady = function (file) {
	// 		var upload;
	// 		console.log(file);
	// 		this._dialog.progress(0);
	// 		this._dialog.state('uploading');
	// 		upload = (function (_this) {
	// 			return function () {
	// 				var progress;
	// 				progress = _this._dialog.progress();
	// 				progress += 1;
	// 				if (progress <= 100) {
	// 					_this._dialog.progress(progress);
	// 					return _this._uploadingTimeout = setTimeout(upload, 25);
	// 				} else {
	// 					return _this._dialog.populate(ImageUploader.imagePath, ImageUploader.imageSize);
	// 				}
	// 			};
	// 		})(this);
	// 		return this._uploadingTimeout = setTimeout(upload, 25);
	// 	};
	//
	// 	ImageUploader.prototype._onMount = function () {
	// 	};
	//
	// 	ImageUploader.prototype._onRotateCCW = function () {
	// 		var clearBusy;
	// 		this._dialog.busy(true);
	// 		clearBusy = (function (_this) {
	// 			return function () {
	// 				return _this._dialog.busy(false);
	// 			};
	// 		})(this);
	// 		return setTimeout(clearBusy, 1500);
	// 	};
	//
	// 	ImageUploader.prototype._onRotateCW = function () {
	// 		var clearBusy;
	// 		this._dialog.busy(true);
	// 		clearBusy = (function (_this) {
	// 			return function () {
	// 				return _this._dialog.busy(false);
	// 			};
	// 		})(this);
	// 		return setTimeout(clearBusy, 1500);
	// 	};
	//
	// 	ImageUploader.prototype._onSave = function () {
	// 		var clearBusy;
	// 		this._dialog.busy(true);
	// 		clearBusy = (function (_this) {
	// 			return function () {
	// 				_this._dialog.busy(false);
	// 				return _this._dialog.save(ImageUploader.imagePath, ImageUploader.imageSize, {
	// 					alt: 'Example of bad variable names'
	// 				});
	// 			};
	// 		})(this);
	// 		return setTimeout(clearBusy, 1500);
	// 	};
	//
	// 	ImageUploader.prototype._onUnmount = function () {
	// 	};
	//
	// 	ImageUploader.createImageUploader = function (dialog) {
	// 		return new ImageUploader(dialog);
	// 	};
	//
	// 	return ImageUploader;
	//
	// })();


	// function imageUploader(dialog) {
	// 	var image, xhr, xhrComplete, xhrProgress;
	//
	// 	// Set up the event handlers
	//
	// 	dialog.addEventListener('imageuploader.cancelupload', function () {
	// 		// Cancel the current upload
	//
	// 		// Stop the upload
	// 		if (xhr) {
	// 			xhr.upload.removeEventListener('progress', xhrProgress);
	// 			xhr.removeEventListener('readystatechange', xhrComplete);
	// 			xhr.abort();
	// 		}
	//
	// 		// Set the dialog to empty
	// 		dialog.state('empty');
	// 	});
	//
	// 	dialog.addEventListener('imageuploader.clear', function () {
	// 		// Clear the current image
	// 		dialog.clear();
	// 		image = null;
	// 	});
	//
	// 	dialog.addEventListener('imageuploader.fileready', function (ev) {
	//
	// 		// Upload a file to the server
	// 		var formData;
	// 		var file = ev.detail().file;
	//
	// 		// Define functions to handle upload progress and completion
	// 		xhrProgress = function (ev) {
	// 			// Set the progress for the upload
	// 			dialog.progress((ev.loaded / ev.total) * 100);
	// 		};
	//
	// 		xhrComplete = function (ev) {
	// 			var response;
	//
	// 			// Check the request is complete
	// 			if (ev.target.readyState != 4) {
	// 				return;
	// 			}
	//
	// 			// Clear the request
	// 			xhr = null;
	// 			xhrProgress = null;
	// 			xhrComplete = null;
	//
	// 			// Handle the result of the upload
	// 			if (parseInt(ev.target.status) == 200) {
	// 				// Unpack the response (from JSON)
	// 				response = JSON.parse(ev.target.responseText);
	//
	// 				// Store the image details
	// 				image = {
	// 					size: response.size,
	// 					url: response.url
	// 				};
	//
	// 				// Populate the dialog
	// 				dialog.populate(image.url, image.size);
	//
	// 			} else {
	// 				// The request failed, notify the user
	// 				new ContentTools.FlashUI('no');
	// 			}
	// 		};
	//
	// 		// Set the dialog state to uploading and reset the progress bar to 0
	// 		dialog.state('uploading');
	// 		dialog.progress(0);
	//
	// 		// Build the form data to post to the server
	// 		formData = new FormData();
	// 		formData.append('image', file);
	//
	// 		// Make the request
	// 		xhr = new XMLHttpRequest();
	// 		xhr.upload.addEventListener('progress', xhrProgress);
	// 		xhr.addEventListener('readystatechange', xhrComplete);
	// 		xhr.open('POST', 'content-api/image/upload', true);
	// 		xhr.send(formData);
	// 	});
	//
	// 	function rotateImage(direction) {
	// 		// Request a rotated version of the image from the server
	// 		var formData;
	//
	// 		// Define a function to handle the request completion
	// 		xhrComplete = function (ev) {
	// 			var response;
	//
	// 			// Check the request is complete
	// 			if (ev.target.readyState != 4) {
	// 				return;
	// 			}
	//
	// 			// Clear the request
	// 			xhr = null;
	// 			xhrComplete = null;
	//
	// 			// Free the dialog from its busy state
	// 			dialog.busy(false);
	//
	// 			// Handle the result of the rotation
	// 			if (parseInt(ev.target.status) == 200) {
	// 				// Unpack the response (from JSON)
	// 				response = JSON.parse(ev.target.responseText);
	//
	// 				// Store the image details (use fake param to force refresh)
	// 				image = {
	// 					size: response.size,
	// 					url: response.url + '?_ignore=' + Date.now()
	// 				};
	//
	// 				// Populate the dialog
	// 				dialog.populate(image.url, image.size);
	//
	// 			} else {
	// 				// The request failed, notify the user
	// 				new ContentTools.FlashUI('no');
	// 			}
	// 		};
	//
	// 		// Set the dialog to busy while the rotate is performed
	// 		dialog.busy(true);
	//
	// 		// Build the form data to post to the server
	// 		formData = new FormData();
	// 		formData.append('url', image.url);
	// 		formData.append('direction', direction);
	//
	// 		// Make the request
	// 		xhr = new XMLHttpRequest();
	// 		xhr.addEventListener('readystatechange', xhrComplete);
	// 		xhr.open('POST', 'content-api/image/rotate', true);
	// 		xhr.send(formData);
	// 	}
	//
	// 	dialog.addEventListener('imageuploader.rotateccw', function () {
	// 		rotateImage('CCW');
	// 	});
	//
	// 	dialog.addEventListener('imageuploader.rotatecw', function () {
	// 		rotateImage('CW');
	// 	});
	//
	// 	dialog.addEventListener('imageuploader.save', function () {
	// 		var crop, cropRegion, formData;
	//
	// 		// Define a function to handle the request completion
	// 		xhrComplete = function (ev) {
	// 			// Check the request is complete
	// 			if (ev.target.readyState !== 4) {
	// 				return;
	// 			}
	//
	// 			// Clear the request
	// 			xhr = null;
	// 			xhrComplete = null;
	//
	// 			// Free the dialog from its busy state
	// 			dialog.busy(false);
	//
	// 			// Handle the result of the rotation
	// 			if (parseInt(ev.target.status) === 200) {
	// 				// Unpack the response (from JSON)
	// 				var response = JSON.parse(ev.target.responseText);
	//
	// 				// Trigger the save event against the dialog with details of the
	// 				// image to be inserted.
	// 				dialog.save(
	// 					response.url,
	// 					response.size,
	// 					{
	// 						'alt': response.alt,
	// 						'data-ce-max-width': 800
	// 					});
	//
	// 			} else {
	// 				// The request failed, notify the user
	// 				new ContentTools.FlashUI('no');
	// 			}
	// 		};
	//
	// 		// Set the dialog to busy while the rotate is performed
	// 		dialog.busy(true);
	//
	// 		// Build the form data to post to the server
	// 		formData = new FormData();
	// 		formData.append('url', image.url);
	//
	// 		// Set the width of the image when it's inserted, this is a default
	// 		// the user will be able to resize the image afterwards.
	// 		formData.append('width', 600);
	//
	// 		// Check if a crop region has been defined by the user
	// 		if (dialog.cropRegion()) {
	// 			formData.append('crop', dialog.cropRegion());
	// 		}
	//
	// 		// Make the request
	// 		xhr = new XMLHttpRequest();
	// 		xhr.addEventListener('readystatechange', xhrComplete);
	// 		xhr.open('POST', 'content-api/image/insert', true);
	// 		xhr.send(formData);
	// 	});
	//
	// }
	//
	// // window.ImageUploader = ImageUploader;
	// // ContentTools.IMAGE_UPLOADER = ImageUploader.createImageUploader;
	// ContentTools.IMAGE_UPLOADER = imageUploader;


	// So this little bundle of variables is required because I'm using CoffeeScript
	// constructs and this code will potentially not have access to these.
	var __slice = [].slice,
		__indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; },
		__hasProp = {}.hasOwnProperty,
		__extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
		__bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

	// Define out custom image tool
	var CustomImageTool = (function(_super) {
		__extends(CustomImageTool, _super);

		function CustomImageTool(dialog) {
			return CustomImageTool.__super__.constructor.apply(this, arguments);
		}

		// Register the tool with ContentTools (in this case we overwrite the
		// default image tool).
		ContentTools.ToolShelf.stow(CustomImageTool, 'image');

		// Set the label and icon we'll use
		CustomImageTool.label = 'Image';
		CustomImageTool.icon = 'image';

		CustomImageTool.canApply = function(element, selection) {
			// So long as there's an image defined we can alwasy insert an image
			return true;
		};

		CustomImageTool.apply = function(element, selection, callback) {

			// First define a function that we can send the custom media manager
			// when an image is ready to insert.
			function _insertImage(url, width, height) {
				// Once the user has selected an image insert it
				if (element.type() === 'Image') {
					element.src(url,width,height,true);
				} else if (element.type() === 'ImageFixture') {
					element.src(url);
				} else {
					// Create the image element
					var image = new ContentEdit.Image({src: url,width: width,height:height});
					// Insert the image
					// console.log(element.type());
					var insertAt = CustomImageTool._insertAt(element);
					var node = insertAt[0];
					var index = insertAt[1];
					node.parent().attach(image, index);
					image.size([width,height],true);
					image.focus();
				}
				// Call the given tool callback
				return callback(true);
			}

			// Make the new function accessible to parent frame
			window.parent.MediaManager = {_insertImage: _insertImage};
			window.parent.bus.$emit('openImageSelector');
		};

		return CustomImageTool;

	})(ContentTools.Tool);

	ContentTools.DEFAULT_MAX_ELEMENT_WIDTH = 1200;

	// ContentTools.DEFAULT_TOOLS[2] = [
	// 	'image',
	// 	'video',
	// 	'preformatted'
	// ];

	ContentTools.LinkDialog = (function(_super) {
		var NEW_WINDOW_TARGET;

		__extends(LinkDialog, _super);

		NEW_WINDOW_TARGET = '_blank';

		function LinkDialog(href, target, anchor) {
			if (href == null) {
				href = '';
			}
			if (target == null) {
				target = '';
			}
			LinkDialog.__super__.constructor.call(this);
			this._href = href;
			this._target = target;
			this.element = anchor;
		}

		LinkDialog.prototype.mount = function() {
			LinkDialog.__super__.mount.call(this);
			this._domInput = document.createElement('input');
			this._domInput.setAttribute('class', 'ct-anchored-dialog__input');
			this._domInput.setAttribute('name', 'href');
			this._domInput.setAttribute('placeholder', ContentEdit._('Enter a link') + '...');
			this._domInput.setAttribute('type', 'text');
			this._domInput.setAttribute('value', this._href);
			this._domElement.appendChild(this._domInput);
			this._domPageButton = this.constructor.createDiv(['ct-anchored-dialog__page-button']);
			this._domElement.appendChild(this._domPageButton);
			this._domTargetButton = this.constructor.createDiv(['ct-anchored-dialog__target-button']);
			this._domElement.appendChild(this._domTargetButton);
			if (this._target === NEW_WINDOW_TARGET) {
				ContentEdit.addCSSClass(this._domTargetButton, 'ct-anchored-dialog__target-button--active');
			}
			this._domSettingsButton = this.constructor.createDiv(['ct-anchored-dialog__settings-button']);
			this._domElement.appendChild(this._domSettingsButton);
			this._domButton = this.constructor.createDiv(['ct-anchored-dialog__button']);
			this._domElement.appendChild(this._domButton);
			return this._addDOMEventListeners();
		};

		LinkDialog.prototype.save = function() {

			var detail = {};

			if (this.element._attributes)
				detail = this.element._attributes;

			detail.href = this._domInput.value.trim();

			if (this._target)
				detail.target = this._target;

			return this.dispatchEvent(this.createEvent('save', detail));
		};

		LinkDialog.prototype.setLink = function(href,attributes) {
			this._domInput.value = href;
			if (attributes) {
				var name,value;
				for (name in attributes) {
					value = attributes[name];
					this.element.attr(name, value);
				}
			}
			return this.save();
		};

		LinkDialog.prototype.show = function() {
			LinkDialog.__super__.show.call(this);
			this._domInput.focus();
			if (this._href) {
				return this._domInput.select();
			}
		};

		LinkDialog.prototype.unmount = function() {
			if (this.isMounted()) {
				this._domInput.blur();
			}
			LinkDialog.__super__.unmount.call(this);
			this._domButton = null;
			return this._domInput = null;
		};

		LinkDialog.prototype._addDOMEventListeners = function() {
			this._domInput.addEventListener('keypress', (function(_this) {
				return function(ev) {
					if (ev.keyCode === 13) {
						return _this.save();
					}
				};
			})(this));
			this._domTargetButton.addEventListener('click', (function(_this) {
				return function(ev) {
					ev.preventDefault();
					if (_this._target === NEW_WINDOW_TARGET) {
						_this._target = '';
						return ContentEdit.removeCSSClass(_this._domTargetButton, 'ct-anchored-dialog__target-button--active');
					} else {
						_this._target = NEW_WINDOW_TARGET;
						return ContentEdit.addCSSClass(_this._domTargetButton, 'ct-anchored-dialog__target-button--active');
					}
				};
			})(this));
			this._domPageButton.addEventListener('click', (function(_this) {
				return function(ev) {
					ev.preventDefault();
					function _insertLink(href,attr){
						_this.setLink(href,attr)
					}
					window.parent.LinkManager = {_insertLink: _insertLink};
					window.parent.bus.$emit('openPageSelector');
				};
			})(this));
			this._domSettingsButton.addEventListener('click', (function(_this) {
				return function(ev) {
					var app, dialog, modal;
					ev.preventDefault();
					app = ContentTools.EditorApp.get();
					modal = new ContentTools.ModalUI();
					delete _this.element._attributes['href'];
					delete _this.element._attributes['target'];
					dialog = new ContentTools.PropertiesDialog(_this.element);
					dialog.addEventListener('cancel', (function() {
						return function() {
							modal.hide();
							dialog.hide();
							if (_this.element.restoreState) {
								return _this.element.restoreState();
							}
						};
					})(this));
					dialog.addEventListener('save', (function() {
						return function(ev) {
							var applied, attributes, className, classNames, cssClass, detail, name, styles, value, _i, _j, _len, _len1, _ref, _ref1;
							detail = ev.detail();
							attributes = detail.changedAttributes;
							styles = detail.changedStyles;
							for (name in attributes) {
								value = attributes[name];
								if (name === 'class') {
									if (value === null) {
										value = '';
									}
									classNames = {};
									_ref = value.split(' ');
									for (_i = 0, _len = _ref.length; _i < _len; _i++) {
										className = _ref[_i];
										className = className.trim();
										if (!className) {
											continue;
										}
										classNames[className] = true;
										if (!_this.element.hasCSSClass(className)) {
											_this.element.addCSSClass(className);
										}
									}
									_ref1 = _this.element.attr('class').split(' ');
									for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
										className = _ref1[_j];
										className = className.trim();
										if (classNames[className] === void 0) {
											_this.element.removeCSSClass(className);
										}
									}
								} else {
									if (value === null) {
										_this.element.removeAttr(name);
									} else {
										_this.element.attr(name, value);
									}
								}
							}
							for (cssClass in styles) {
								applied = styles[cssClass];
								if (applied) {
									_this.element.addCSSClass(cssClass);
								} else {
									_this.element.removeCSSClass(cssClass);
								}
							}
							modal.hide();
							dialog.hide();
							if (_this.element.restoreState) {
								return _this.element.restoreState();
							}
						};
					})(this));
					app.attach(modal);
					app.attach(dialog);
					modal.show();
					return dialog.show();

				};
			})(this));
			return this._domButton.addEventListener('click', (function(_this) {
				return function(ev) {
					ev.preventDefault();
					return _this.save();
				};
			})(this));
		};

		return LinkDialog;

	})(ContentTools.AnchoredDialogUI);


	ContentEdit.Anchor = (function(_super) {
		__extends(Anchor, _super);
		function Anchor(content,attr) {
			Anchor.__super__.constructor.call(this, 'a', attr, content);
		}
		Anchor.prototype.cssTypeName = function() {
			return 'anchor';
		};
		Anchor.prototype.type = function() {
			return 'Anchor';
		};
		Anchor.prototype.setParent = function(parent) {
			this.parent = parent;
		};
		Anchor.prototype.nextSibling = function() {
			return false;
		};
		return Anchor;
	})(ContentEdit.Text);


	ContentTools.Tools.Subsubheading = (function(_super) {
		__extends(Subsubheading, _super);
		function Subsubheading() {
			return Subsubheading.__super__.constructor.apply(this, arguments);
		}
		ContentTools.ToolShelf.stow(Subsubheading, 'subsubheading');
		Subsubheading.label = 'H3';
		Subsubheading.icon = 'subsubheading';
		Subsubheading.tagName = 'h3';
		return Subsubheading;

	})(ContentTools.Tools.Heading);

	ContentTools.DEFAULT_TOOLS[1] = ['heading', 'subheading', 'subsubheading', 'paragraph', 'unordered-list', 'ordered-list', 'table', 'indent', 'unindent', 'line-break'];
	// ContentTools.INLINE_TAGS = ['address', 'b', 'code', 'del', 'em', 'i', 'ins', 'span', 'strong', 'sup', 'u'];

	let ll = {
		en: {
			headline_default: 'Normal',
			headline_bullet: 'Bullet',
			headline_divider: 'Divider',
			headline_light: 'Light',
			card_title: 'Card title',
			a_action_btn: 'Action-Button',
			p_lead: 'Lead text',
			p_meta: 'Meta text',
			p_uppercase: 'Uppercase',
			table: 'Table',
			table_divider: 'Divider',
			table_hover: 'Hover rows',
			table_striped: 'Striped rows',
			table_middle: 'Center middle',
			table_responsive: 'Break responsive',
			table_shrink: 'Shrink to fit content',
			table_nowrap: 'Text nowrap',
			table_expand: 'Expand width',
			table_w_small: 'Small width',
			table_w_medium: 'Medium width',
			table_w_large: 'Large width',
			iframe_responsive_w: 'responsive Width',
			iframe_responsive_h: 'responsive Height',
			iframe_preserver_w: 'Preserve width',
		},
		de: {
			headline_default: 'Normal',
			headline_bullet: 'Bullet',
			headline_divider: 'Trenner',
			headline_light: 'Leicht',
			card_title: 'Titel',
			a_action_btn: 'Action-Button',
			p_lead: 'Hervorheben',
			p_meta: 'Abschwächen',
			p_uppercase: 'Großschreibung',
			p_text_mark_primary: 'Primär markiert',
			p_text_mark_secondary: 'Sekundär markiert',
			table: 'Tabelle',
			table_divider: 'Trenner',
			table_hover: 'Zeilen hervorheben',
			table_striped: 'gestreifte Zeilen',
			table_middle: 'mittig zentrieren',
			table_responsive: 'responsiv umbrechen',
			table_shrink: 'auf Inhalt schrumpfen',
			table_nowrap: 'Text nicht automatisch umbrechen',
			table_expand: 'Breite ausdehnen',
			table_w_small: 'Breite schmal',
			table_w_medium: 'Breite mittel',
			table_w_large: 'Breite groß',
			iframe_responsive_w: 'responsive Breite',
			iframe_responsive_h: 'responsive Höhe',
			iframe_preserver_w: 'Breite beibehalten',
		},
	};

	let dict = ll[lang];

	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.headline_default, 'h1-regular', ['h1'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.headline_bullet, 'uk-heading-bullet', ['h1','h2','h3'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.headline_divider, 'uk-heading-divider', ['h1','h2'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.card_title, 'uk-card-title', ['h2','h3'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.headline_light, 'uk-card-title', ['h2'])
	]);

	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.a_action_btn, 'action-button', ['a'])
	]);

	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.p_lead, 'uk-text-lead', ['p'])
	]);

	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.p_meta, 'uk-text-meta', ['p'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.p_uppercase, 'uk-text-uppercase', ['p'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.p_text_mark_primary, 'text-mark-primary', ['p'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.p_text_mark_secondary, 'text-mark-secondary', ['p'])
	]);

	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table, 'uk-table', ['table'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_divider, 'uk-table-divider', ['table'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_hover, 'uk-table-hover', ['table'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_striped, 'uk-table-striped', ['table'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_middle, 'uk-table-middle', ['table'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_responsive, 'uk-table-responsive', ['table'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_shrink, 'uk-table-shrink', ['th'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_nowrap, 'uk-text-nowrap', ['td','th'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_expand, 'uk-table-expand', ['th'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_w_small, 'uk-width-small', ['th'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_w_medium, 'uk-width-medium', ['th'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.table_w_large, 'uk-width-large', ['th'])
	]);

	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.iframe_responsive_w, 'uk-responsive-width', ['iframe'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.iframe_responsive_h, 'uk-responsive-height', ['iframe'])
	]);
	ContentTools.StylePalette.add([
		new ContentTools.Style(dict.iframe_preserver_w, 'uk-preserve-width', ['iframe'])
	]);

	tagNames = ContentEdit.TagNames.get();
	tagNames.register(ContentEdit.Text,'figcaption');
	// tagNames.register(ContentEdit.Anchor,'a');

	var editor = ContentTools.EditorApp.get();
	var flowMgr = ContentFlow.FlowMgr.get();
	var flowsQuery;

	editor.init('[data-region], [data-fixture]', 'data-name');

	let api = new ContentFlow.BaseAPI(basePath+'config-api/content/'+pageId+'/');

	flowMgr.init(flowsQuery = '[data-cf-flow]', api);
	flowMgr.addEventListener('change', function() {
		// send event to parent frame
		var event = new CustomEvent('content-saved')
		window.parent.document.dispatchEvent(event);
	})
	// flowMgr.init(flowsQuery = '[data-cf-flow]', api = new MockAPI(basePath));
	editor.addEventListener('start', function (ev) {
		window.dispatchEvent(createEvent('editor_start',false,true));
	});
	editor.addEventListener('stop', function (ev) {
		window.dispatchEvent(createEvent('editor_stop',false,true));
	});

	editor.addEventListener('saved', function (ev) {
		var name, payload, regions, xhr;

		// Check that something changed
		regions = ev.detail().regions;
		if (Object.keys(regions).length == 0) {
			return;
		}
		// Set the editor as busy while we save our changes
		this.busy(true);

		// console.log(regions);
		// Collect the contents of each region into a FormData instance
		payload = new FormData();
		for (name in regions) {
			if (regions.hasOwnProperty(name)) {
				payload.append(name, regions[name]);
			}
		}
		// Send the update content to the server to be saved
		function onStateChange(ev) {
			// Check if the request is finished
			if (ev.target.readyState == 4) {
				editor.busy(false);
				if (ev.target.status == '200') {
					// Save was successful, notify the user with a flash
					new ContentTools.FlashUI('ok');

					// send event to parent frame
					var event = new CustomEvent('content-saved')
					window.parent.document.dispatchEvent(event)
				} else {
					// Save failed, notify the user with a flash
					new ContentTools.FlashUI('no');
				}
			}
		}

		xhr = new XMLHttpRequest();
		xhr.addEventListener('readystatechange', onStateChange);
		xhr.open('POST', 'config-api/content/'+pageId+'/save');
		xhr.send(payload);
	});

	// load language
	xhr = new XMLHttpRequest();
	xhr.open('GET', 'ext/sugar/ui/editor/contenttools_v1_6_10/ui/lib/ct/translations/'+lang+'.json', true);
	function onStateChange (ev) {
		var translations;
		if (ev.target.readyState == 4) {
			translations = JSON.parse(ev.target.responseText);
			ContentEdit.addTranslations(lang, translations);
			ContentEdit.LANGUAGE = lang;
		}
	}
	xhr.addEventListener('readystatechange', onStateChange);
	xhr.send(null);

});



// (function () {

//
// 	window.onload = function () {
// 		var FIXTURE_TOOLS, IMAGE_FIXTURE_TOOLS, LINK_FIXTURE_TOOLS, editor, req;
// 		ContentTools.IMAGE_UPLOADER = ImageUploader.createImageUploader;
// 		ContentTools.StylePalette.add([new ContentTools.Style('By-line', 'article__by-line', ['p']), new ContentTools.Style('Caption', 'article__caption', ['p']), new ContentTools.Style('Example', 'example', ['pre']), new ContentTools.Style('Example + Good', 'example--good', ['pre']), new ContentTools.Style('Example + Bad', 'example--bad', ['pre'])]);
// 		editor = ContentTools.EditorApp.get();
// 		editor.init('[data-editable], [data-fixture]', 'data-name');
// 		editor.addEventListener('saved', function (ev) {
// 			var saved;
// 			console.log(ev.detail().regions);
// 			if (Object.keys(ev.detail().regions).length === 0) {
// 				return;
// 			}
// 			editor.busy(true);
// 			saved = (function (_this) {
// 				return function () {
// 					editor.busy(false);
// 					return new ContentTools.FlashUI('ok');
// 				};
// 			})(this);
// 			return setTimeout(saved, 2000);
// 		});
// 		FIXTURE_TOOLS = [['undo', 'redo', 'remove']];
// 		IMAGE_FIXTURE_TOOLS = [['undo', 'redo', 'image']];
// 		LINK_FIXTURE_TOOLS = [['undo', 'redo', 'link']];
// 		ContentEdit.Root.get().bind('focus', function (element) {
// 			var tools;
// 			if (element.isFixed()) {
// 				if (element.type() === 'ImageFixture') {
// 					tools = IMAGE_FIXTURE_TOOLS;
// 				} else if (element.tagName() === 'a') {
// 					tools = LINK_FIXTURE_TOOLS;
// 				} else {
// 					tools = FIXTURE_TOOLS;
// 				}
// 			} else {
// 				tools = ContentTools.DEFAULT_TOOLS;
// 			}
// 			if (editor.toolbox().tools() !== tools) {
// 				return editor.toolbox().tools(tools);
// 			}
// 		});
// 		req = new XMLHttpRequest();
// 		req.overrideMimeType('application/json');
// 		req.open('GET', 'https://raw.githubusercontent.com/GetmeUK/ContentTools/master/translations/lp.json', true);
// 		return req.onreadystatechange = function (ev) {
// 			var translations;
// 			if (ev.target.readyState === 4) {
// 				translations = JSON.parse(ev.target.responseText);
// 				ContentEdit.addTranslations('lp', translations);
// 				return ContentEdit.LANGUAGE = 'lp';
// 			}
// 		};
// 	};
//
// }).call(this);
