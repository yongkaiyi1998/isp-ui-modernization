const puppeteer = require('puppeteer');
var options = ["--no-sandbox","--disable-web-security"];
var runheadless = true;
var debug = false;
var UserAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36";
var loadTimeout=45000; 
var TEST_ADDR = false;
var PROXY = false;

var SEARCH_FOR = "";

var FILE_NAME = "example.pdf";

var CHROME_PATH = "";

var ORIENTATION = 'portrait';

var FORMAT = 'A4';

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
        case 'chrome_path':
            CHROME_PATH=args[idx];
            break;
        case 'orientation':
            ORIENTATION = args[idx];
            break;
        case 'format':
            FORMAT = args[idx];
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

        await page.goto(TEST_ADDR, {waitUntil: 'networkidle0'});
        await page.pdf({ path: FILE_NAME, format: FORMAT, printBackground: true, landscape: (ORIENTATION=='landscape') });

    } catch (err) {
        console.log(err);
    }
    await browser.close();
});