(function($) {
	$.extend({
		jGet: function(handle, url, type) {
			if ( !handle ) { // no handle was specified when calling the object
				alert('No handle for jGet was specified');
				return false; 
			}
			var searchString = window.location.search; // set variable to the search part of the url
			var hashString = window.location.hash; // set variable to the hash part of the url
			if ( url !== undefined ) {
				searchString = url;
				hashString = url;
			}
			var searchPos = searchString.indexOf(handle); // get the position of our handle within the searchString variable
			var hashPos = hashString.indexOf(handle); // get the position of our handle within the hashString variable
			if ( !type ) { // type was not specified, so we return $_GET value first, then hash value after			
				if ( hashPos != -1 ) { // if we didn't find the handle within the searchString variable, try looking for it in the hashString
					var string = hashString;
					var firstPos = hashPos;
				} else if ( searchPos != -1 ) { // if we found the handle within the searchString variable, then set the general variables accordingly
					var string = searchString;
					var firstPos = searchPos;
				} else { // we didn't find the handle at all, so return null
					return null;	
				}
			} else { // if we have specified the type, then only return the value from that type
				switch (type) { // switch on the specified type
					case 'search': // only look in the searchString variable
						var string = searchString;
						var firstPos = searchPos;
					break;
					case 'hash': // only look in the hashString variable
						var string = hashString;
						var firstPos = hashPos;
					break;
					default: // the type didn't match two two accepted values, so we return null
						return null;
				}
			}
			var stringSliced = string.slice(firstPos); // slice the string and only return the portion after the handle text (including the handle)
			var andPos = stringSliced.indexOf('&'); // get the position of the first "&" sign within our stringSpliced variable
			if ( andPos != -1 ) { // if we found an "&" sign, then we'll return the value contained between our handle and the first "&" sign, excluding the "=" sign
				value = stringSliced.slice(handle.length + 1, andPos);
			} else { // if we didn't find an "&" sign, then return everthing after our handle, excluding the "=" sign
				value = stringSliced.slice(handle.length + 1);
			}
			return value; // return the value for the specified handle!
		}
	});
})(jQuery);


