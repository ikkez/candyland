var Language = function (e, t, r, s) {
	this.language = e, this.months = t, this.monthsAbbr = r, this.days = s, this.rtl = !1, this.ymd = !1, this.yearSuffix = ""
}, prototypeAccessors = {
	language: {configurable: !0},
	months: {configurable: !0},
	monthsAbbr: {configurable: !0},
	days: {configurable: !0}
};
prototypeAccessors.language.get = function () {
	return this._language
}, prototypeAccessors.language.set = function (e) {
	if ("string" != typeof e) throw new TypeError("Language must be a string");
	this._language = e
}, prototypeAccessors.months.get = function () {
	return this._months
}, prototypeAccessors.months.set = function (e) {
	if (12 !== e.length) throw new RangeError("There must be 12 months for " + this.language + " language");
	this._months = e
}, prototypeAccessors.monthsAbbr.get = function () {
	return this._monthsAbbr
}, prototypeAccessors.monthsAbbr.set = function (e) {
	if (12 !== e.length) throw new RangeError("There must be 12 abbreviated months for " + this.language + " language");
	this._monthsAbbr = e
}, prototypeAccessors.days.get = function () {
	return this._days
}, prototypeAccessors.days.set = function (e) {
	if (7 !== e.length) throw new RangeError("There must be 7 days for " + this.language + " language");
	this._days = e
}, Object.defineProperties(Language.prototype, prototypeAccessors);
var de = new Language("German", ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"], ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"], ["So.", "Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa."]);
export default de;
