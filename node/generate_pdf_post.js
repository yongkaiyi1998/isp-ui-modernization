const puppeteer = require('puppeteer');
const querystring = require('querystring');
var options = ["--no-sandbox","--disable-web-security"];
var runheadless = true;
var debug = false;
var UserAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36";
var loadTimeout=45000; 
var TEST_ADDR = false;
var PROXY = false;

var SEARCH_FOR = "";

var FILE_NAME = "example.pdf";

var POST_VALUES = "";

var CHROME_PATH = "";

const args = require('minimist')(process.argv.slice(2));
for (var idx in args) {
    switch(idx) {
        case 'debug':
            console.log('debug on');
            debug=true;
            break;
        case 'show':
            if (debug) console.log('show browser on');
            runheadless=false;
            break
        case 'url':
            TEST_ADDR=args[idx];
            break;
        case 'proxy':
            PROXY=args[idx];
            break;
        case 'search':
            SEARCH_FOR=args[idx];
            break;
        case 'file_name':
            FILE_NAME=args[idx];
            break;
        case 'post_values':
            POST_VALUES=args[idx];
            break;
        case 'chrome_path':
            CHROME_PATH=args[idx];
            break;
    }
}

const windowSet = ((page, name, value) =>
  page.evaluateOnNewDocument(`
    Object.defineProperty(window, '${name}', {
      get() {
        return '${value}'
      }
    })
  `)
);

if (!TEST_ADDR) {
    console.log("Usage: node httpget.js --url={url} --search={selectors} [--debug] [--proxy={ip:port}] [--show]");
    process.exit();
}

if (debug) console.log('URL [ '+TEST_ADDR+' ]');
if (PROXY!==false) {
    options.push('--proxy-server='+PROXY);
    options.push('--proxy-bypass-list="'+API_ADDR+'"');
    if (debug) console.log('Proxy [ '+PROXY+' ]');
}

puppeteer.launch({headless: runheadless, args: options, executablePath: CHROME_PATH}).then(async browser => {
    try {
        const page = await browser.newPage();
        page.setUserAgent(UserAgent);
        await page.setViewport({width: 1024, height: 768});
          if (debug) console.log('Loading...');

        await page.setExtraHTTPHeaders({
            'no-auth':'1',
        });

        // Allows you to intercept a request; must appear before
        // your first page.goto()
        await page.setRequestInterception(true);

        //await page.goto(TEST_ADDR, {waitUntil: 'networkidle0'});
        //await page.pdf({ path: 'node/temp/'+FILE_NAME, format: 'A4' });

        // Request intercept handler... will be triggered with 
        // each page.goto() statement
        //let postData = {a: 1, b: 2};
        page.once('request', request => {
            var data = {
                'method': 'POST',
                'postData': querystring.stringify(JSON.parse(POST_VALUES)),
                'headers': {
                    ...request.headers(),
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
            };

            request.continue(data);

            // Immediately disable setRequestInterception, or all other requests will hang
            page.setRequestInterception(false);
        });

        // Navigate, trigger the intercept, and resolve the response
        const response = await page.goto(TEST_ADDR, {waitUntil: 'networkidle0'});   
        await page.pdf({ path: FILE_NAME, format: 'A4' }); 
        const responseBody = await response.text();
        //console.log(responseBody);

        // Close the browser - done! 
        await browser.close();

    } catch (err) {
        console.log(err);
    }
    await browser.close();
});