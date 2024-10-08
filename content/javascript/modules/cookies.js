$.cookie = function(name, value, options) {
    if (typeof value === "undefined") {
        let cookieStrings = document.cookie.split(";")
        for (let i = 0; i < cookieStrings.length; i++) {
            let cookieString = cookieStrings[i];
            let cookieParts = cookieString.split("=");
            let cookieName = cookieParts[0].trim();
            let cookieValue = cookieParts[1] ? cookieParts[1].trim() : null;

            if (cookieName === name) {
                return decodeURIComponent(cookieValue);
            }
        }
        return undefined;
    } else {
        options = options || {};

        if (value === null) {
            value = '';
            options.expires = -1;
        }

        let expires = "";
        if (typeof options.expires === 'number') {
            let date = new Date();
            date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        } else if (options.expires instanceof Date) {
            expires = "; expires=" + options.expires.toUTCString();
        }

        let path = options.path ? '; path=' + options.path : '';
        let domain = options.domain ? '; domain=' + options.domain : '';
        let secure = options.secure ? '; secure' : '';

        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    }
}
