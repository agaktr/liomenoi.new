/**
 * MOBILE DETECT START
 * This checks if a media query element is visible so we can determine if the user
 * is on a mobile or not.
 */

export function mobileDetect() {
    // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
    let el = document.getElementById("mobile-detect"),
        style = window.getComputedStyle(el)

    window.isMobile = style.getPropertyValue("display") === "none"

    // We listen to the resize event
    window.addEventListener('resize', () => {

        window.isMobile = style.getPropertyValue("display") === "none"
    });
}

/**
 * MOBILE DETECT END
 */