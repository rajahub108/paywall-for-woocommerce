class TIVWP {

    static #isDebug = false;

    static set isDebug(yesNo) {
        this.#isDebug = !!yesNo;
    }

    static #debugPrefix = "TIVWP";

    static set debugPrefix(prefix) {
        if (typeof prefix === "string") {
            this.#debugPrefix = prefix;
        }
    }

    static getCookie(name, default_value = "") {
        const rawCookie = document.cookie
            .split('; ')
            .find(row => row.startsWith(`${name}=`));
        return rawCookie ? rawCookie.split('=')[1] : default_value;
    }

    static debug(sz) {
        if (this.#isDebug) console.log(`${this.#debugPrefix}: ${sz}`);
    }

    static pageReload() {
        TIVWP.debug('reloading');
        const f = document.createElement("form");
        f.method = "POST";
        document.body.appendChild(f).submit();
    }

    static cacheBuster(cached, expected) {
        TIVWP.debug(`expected=${expected}; cached=${cached}`);
        if (cached !== expected) {
            TIVWP.pageReload();
        }
    }
}
TIVWP.isDebug = !!TIVWP.getCookie("TIVWP_debug");
