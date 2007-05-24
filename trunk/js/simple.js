// These are just some quick JS helper functions if you don't want to include all of prototype.js

// Remove me if using prototype.js
function $(id) { return document.getElementById("id"); }

function isWhitespace(el) {
	return !(/[^\t\n\r ]/.test(el.data));
}
function isIgnorable(el) { 
	return (el.nodeType == 8) || ((el.nodeType == 3) && isWhitespace(el));
}
function nodeBefore(el) {
	while((el = el.previousSibling))
		if(!isIgnorable(el)) return el;
	return null;
}
function nodeAfter(el) {
	while((el = el.nextSibling))
		if(!isIgnorable(el)) return el;
	return null;
}
function firstchild(el) {
	el = el.firstChild;
	while(el) {
		if(!isIgnorable(el)) return el;
		el = el.nextSibling;
	}
	return null;
}
function lastChild(el) {
	el = el.lastChild;
	while(el) {
		if(!isIgnorable(el)) return el;
		el = el.previousSibling;
	}
	return null;
}
function getElementsByClassName(el, class) {
    a     = [];
    regex = new RegExp('\\b' + class + '\\b');
    els   = el.getElementsByTagName("*");
	for(el in els)
        if(regex.test(els[el].className))
			a.push(els[el]);
    return a;
}
function getElementsByAttribute(el, tagName, attributeName, attributeValue) {
    a = [];
    els = (tagName == "*" && el.all) ? el.all : el.getElementsByTagName(tagName);
    attributeValue = (typeof attributeValue != "undefined") ? new RegExp("(^|\\s)" + attributeValue + "(\\s|$)", "i") : null;
    current = null;
    attribute = null;
    for(i = 0; i < els.length; i++) {
        current = els[i];
        attribute = current.getAttribute && current.getAttribute(attributeName);
        if(typeof attribute == "string" && attribute.length > 0) {
            if(typeof attributeValue == "undefined" || (attributeValue && attributeValue.test(attribute)))
                a.push(current);
        }
    }
    return a;
}