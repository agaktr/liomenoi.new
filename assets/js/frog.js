/**
 * FROG START
 * Some serious fun at first :D
 * This is a frog that jumps around the console when you open it.
 */
export function frog() {
    (function doTheThing() {
        // Act on either the window.console, or the normal console.
        let con = console;
        if (typeof(window) !== 'undefined') {
            con = window.console;
        }

        con.frog = function() {

            let i,
                css = "color: green";

            // Should be enough to make this fine in node.
            let inBrowser = (typeof window !== 'undefined');

            // This looks like a frog, right?
            // Taken from here - http://chris.com/ascii/index.php?art=animals/frogs

            let frog;
            if( inBrowser ) {
                frog = ['%c%c',
                    "%c         _,-.  %c",
                    "%c ,-. ,--'  o ) %c",
                    "%c \\(,' '  ,,-' %c",
                    "%c,-.\\-.__,\\\\_%c",
                    "%c\\(`--'    `\\ %c",
                    '%c%c'];
            } else {
                frog = ['',
                    "         _,-.  ",
                    " ,-. ,--'  o ) ",
                    " \\(,' '  ,,-' ",
                    ",-.\\-.__,\\\\_",
                    "\\(`--'    `\\ ",
                    ''];
            }


            // Gets args as a string
            let args = Array.prototype.slice.call(arguments);
            let stringOfArgs = args.join(' ');

            // Add the bubble if there is something to log!
            if( stringOfArgs.length > 0 ) {
                frog[1] = frog[1] + "   ---" + ("-".repeat(stringOfArgs.length)) + "-";
                frog[2] = frog[2] + "-(   " + stringOfArgs + "   )";
                frog[3] = frog[3] + "    ---" + ("-".repeat(stringOfArgs.length)) + "-";
            }

            // Log the frog!
            if( inBrowser ) {
                for( i = 0; i < frog.length; i++ ) {
                    console.log(frog[i], css, "");
                }
            } else {
                for( i = 0; i < frog.length; i++ ) {
                    console.log(frog[i]);
                }
            }

            let styles= [
                "font-size: 14px",
                "font-family: monospace",
                "background: white",
                "display: inline-block",
                "padding: 8px 19px",
                "border: 2px dashed;"
            ].join(";");
            console.log("%c Roses are red 🔥", "color: red;"+styles );
            console.log("%c Violets are blue 🏄", "color: blue;"+styles );
            console.log("%c Don't mess with our code 😨", "color: green; "+styles );
            console.log("%c Because we will find you! 🚀", "color: black; "+styles );
        }
    })();
    console.frog('We start... Ribit..')
}

/**
 * FROG END
 * Done of serious fun :/
 */