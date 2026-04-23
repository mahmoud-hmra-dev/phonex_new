// Quick browser test: does the language dropdown open when clicked?
import puppeteer from "puppeteer-core";
import fs from "node:fs";

// Find Chrome on Windows
function findChrome() {
    const candidates = [
        "C:/Program Files/Google/Chrome/Application/chrome.exe",
        "C:/Program Files (x86)/Google/Chrome/Application/chrome.exe",
        process.env.LOCALAPPDATA + "/Google/Chrome/Application/chrome.exe",
        "C:/Program Files/Microsoft/Edge/Application/msedge.exe",
        "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe",
    ];
    for (const p of candidates) {
        try { if (fs.existsSync(p)) return p; } catch {}
    }
    return null;
}

const exe = findChrome();
if (!exe) {
    console.error("Chrome/Edge not found");
    process.exit(2);
}

const browser = await puppeteer.launch({
    executablePath: exe,
    headless: "new",
    defaultViewport: { width: 1440, height: 900 },
});

try {
    const page = await browser.newPage();

    const logs = [];
    page.on("console", (m) => logs.push(`[${m.type()}] ${m.text()}`));
    page.on("pageerror", (e) => logs.push(`[PAGEERROR] ${e.message}`));

    await page.goto("http://127.0.0.1:8123/phonix", { waitUntil: "domcontentloaded", timeout: 30000 });
    // Give Alpine time to boot
    await new Promise((r) => setTimeout(r, 1500));

    // Wait for Alpine
    await page.waitForFunction(() => !!window.Alpine, { timeout: 10000 });

    const report = {};

    // Find the promo-bar language dropdown button
    // It's inside the "hidden lg:block" promo bar
    const langBtnSel = '.hidden.lg\\:block button[aria-haspopup="menu"]';
    const langBtnCount = await page.$$eval(langBtnSel, (nodes) => nodes.length);
    report.promoLangButtonCount = langBtnCount;

    // Click the first one
    if (langBtnCount > 0) {
        const beforeExpanded = await page.$eval(langBtnSel, (el) => el.getAttribute("aria-expanded"));
        report.beforeClick = { ariaExpanded: beforeExpanded };

        await page.click(langBtnSel);
        await new Promise((r) => setTimeout(r, 200));

        const afterExpanded = await page.$eval(langBtnSel, (el) => el.getAttribute("aria-expanded"));
        report.afterClick = { ariaExpanded: afterExpanded };

        // Check if any dropdown panel is visible (not display:none)
        const panelsVisible = await page.$$eval('[role="menu"]', (panels) =>
            panels.map((p) => ({
                display: getComputedStyle(p).display,
                visibility: getComputedStyle(p).visibility,
                rect: p.getBoundingClientRect().toJSON(),
                parentOverflow: getComputedStyle(p.parentElement).overflow,
                ancestorOverflow: (() => {
                    let e = p.parentElement;
                    while (e && e !== document.body) {
                        const s = getComputedStyle(e);
                        if (s.overflow !== "visible") return `${e.tagName}.${e.className.toString().slice(0,40)}=${s.overflow}`;
                        e = e.parentElement;
                    }
                    return "none";
                })(),
            }))
        );
        report.panelsAfterClick = panelsVisible;
    }

    // Mobile language toggle is lg:hidden — set viewport to mobile to test
    await page.setViewport({ width: 390, height: 844 });
    await page.reload({ waitUntil: "domcontentloaded" });
    await new Promise((r) => setTimeout(r, 1500));

    // Mobile language button
    const mobileLangSel = '.lg\\:hidden button[aria-haspopup="menu"]';
    const mobileLangCount = await page.$$eval(mobileLangSel, (nodes) => nodes.length);
    report.mobileLangButtonCount = mobileLangCount;

    if (mobileLangCount > 0) {
        await page.click(mobileLangSel);
        await new Promise((r) => setTimeout(r, 200));
        const afterMobileClick = await page.$eval(mobileLangSel, (el) => el.getAttribute("aria-expanded"));
        report.mobileAfterClick = { ariaExpanded: afterMobileClick };
        const mobilePanels = await page.$$eval('[role="menu"]', (panels) =>
            panels.map((p) => ({ display: getComputedStyle(p).display, rect: p.getBoundingClientRect().toJSON() }))
        );
        report.mobilePanelsAfterClick = mobilePanels.filter((p) => p.display !== "none");
    }

    console.log(JSON.stringify(report, null, 2));
    if (logs.length) console.log("LOGS:", logs.join("\n"));
} finally {
    await browser.close();
}
