const { chromium } = require('playwright');

const MAX_REVIEWS = 600;
const MAX_TIME_MS = 180000;
const MAX_PAGES = 30;

(async () => {
    const url = process.argv[2];

    if (!url) {
        console.error('URL NOT PROVIDED');
        process.exit(1);
    }

    let browser;

    try {
        browser = await chromium.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu'
            ]
        });

        const context = await browser.newContext();

        await context.route('**/*', route => {
            const type = route.request().resourceType();

            if (
                type === 'image' ||
                type === 'font' ||
                type === 'media' ||
                type === 'stylesheet'
            ) {
                return route.abort();
            }

            route.continue();
        });

        const page = await context.newPage();

        const loadedPages = new Set();
        const reviews = [];

        let totalPages = null;

        page.on('response', async response => {
            const responseUrl = response.url();

            if (!responseUrl.includes('fetchReviews')) {
                return;
            }

            try {
                const json = await response.json();

                const params = json?.data?.params;

                if (!params) {
                    return;
                }

                const pageNumber = params.page;

                if (loadedPages.has(pageNumber)) {
                    return;
                }

                loadedPages.add(pageNumber);

                totalPages = params.totalPages;

                for (const review of json.data.reviews ?? []) {
                    if (reviews.length >= MAX_REVIEWS) {
                        break;
                    }

                    reviews.push({
                        reviewId:
                            review.reviewId ??
                            null,

                        author:
                            review.author?.name ??
                            '',

                        rating:
                            review.rating ??
                            null,

                        text:
                            review.text ??
                            '',

                        date:
                            review.updatedTime ??
                            null
                    });
                }
            } catch {}
        });

        await page.goto(url, {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });

        const title =
            await page
                .locator('h1')
                .first()
                .textContent()
                .catch(() => null);

        const ratingText =
            await page
                .locator(
                    '.business-rating-badge-view__rating-text'
                )
                .first()
                .textContent()
                .catch(() => null);

        const reviewsCounterText =
            await page
                .locator(
                    '[aria-label^="Отзывы"] .tabs-select-view__counter'
                )
                .first()
                .textContent()
                .catch(() => '0');

        const ratingsCounterText =
            await page
                .locator(
                    '.business-rating-amount-view._summary'
                )
                .first()
                .textContent()
                .catch(() => '0');

        const reviewsTab =
            page.locator(
                '[aria-label^="Отзывы"]'
            );

        if (await reviewsTab.count()) {
            await reviewsTab.click();
        }

        const container =
            page
                .locator('.scroll__container')
                .first();

        const startedAt =
            Date.now();

        let idleIterations = 0;
        let previousCount = 0;

        if (await container.count()) {
            while (true) {
                if (
                    Date.now() -
                        startedAt >
                    MAX_TIME_MS
                ) {
                    break;
                }

                if (
                    reviews.length >=
                    MAX_REVIEWS
                ) {
                    break;
                }

                if (
                    loadedPages.size >=
                    MAX_PAGES
                ) {
                    break;
                }

                if (
                    totalPages !==
                        null &&
                    loadedPages.size >=
                        totalPages
                ) {
                    break;
                }

                await container.evaluate(
                    el => {
                        el.scrollTop =
                            el.scrollHeight;
                    }
                );

                await page.waitForTimeout(
                    800
                );

                if (
                    reviews.length ===
                    previousCount
                ) {
                    idleIterations++;
                } else {
                    previousCount =
                        reviews.length;

                    idleIterations = 0;
                }

                if (
                    idleIterations >=
                    4
                ) {
                    break;
                }
            }
        }

        await context.close();

        const result = {
            title,

            rating:
                ratingText
                    ? Number(
                          ratingText.replace(
                              ',',
                              '.'
                          )
                      )
                    : null,

            reviews_count:
                Number(
                    reviewsCounterText.replace(
                        /\D/g,
                        ''
                    )
                ) || 0,

            ratings_count:
                Number(
                    ratingsCounterText.replace(
                        /\D/g,
                        ''
                    )
                ) || 0,

            reviews
        };

        process.stdout.write(
            JSON.stringify(result)
        );

        process.exit(0);
    } catch (e) {
        console.error(e.message);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();