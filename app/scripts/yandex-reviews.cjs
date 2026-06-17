const { chromium } = require('playwright');

const MAX_REVIEWS = 600;
const MAX_TIME_MS = 45000;
const MAX_PAGES = 40;

(async () => {
    const url = process.argv[2];

    if (!url) {
        console.error('URL NOT PROVIDED');
        process.exit(1);
    }

    let browser;

    try {
        browser = await chromium.launch({
            headless: true
        });

        const page = await browser.newPage();

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
                        reviewId: review.reviewId ?? null,

                        author: review.author?.name ?? '',

                        rating: review.rating ?? null,

                        text: review.text ?? '',

                        date: review.updatedTime ?? null
                    });
                }

            } catch {}
        });

        await page.goto(url, {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });

        await page.waitForTimeout(1500);

        const title = await page.locator('h1').first().textContent().catch(() => null);

        if (!title || title.includes('Ошибка 404. Нет такой страницы')) {
            console.error('ORGANIZATION NOT FOUND');
            process.exit(1);
        }

        const ratingText = await page.locator(
                    '.business-rating-badge-view__rating-text'
                ).first().textContent().catch(() => null);

        const ratingsCounterText = await page.locator(
                    '.business-rating-amount-view._summary'
                ).first().textContent().catch(() => '0');

        const reviewsTab = page.locator('[aria-label^="Отзывы"]');

        let reviewsCount = 0;

        if (await reviewsTab.count()) {

            const text = await reviewsTab.textContent();

            reviewsCount = Number(text?.replace(/\D/g, '')) || 0;

            await reviewsTab.click();

            await page.waitForTimeout(1000);

            try {

                const pollResponse = await page.waitForResponse(
                    response => response.url().includes('fetchReviewPollData'),{ timeout: 3000 }
                );

                const poll = await pollResponse.json();

                const pollCount = poll?.data?.reviewsCount;

                if (typeof pollCount === 'number') {
                    reviewsCount = pollCount;
                }

            } catch {}
        }

        if (reviewsCount === 0) {

            const result = { 
                title,

                rating:
                    ratingText ? Number(ratingText.replace(',','.')): null,

                reviews_count: 0,

                ratings_count: Number(ratingsCounterText.replace(/\D/g,'')) || 0,

                reviews: []
            };

            process.stdout.write(JSON.stringify(result));

            return;
        }

        const container = page.locator('.scroll__container').first();

        const startedAt = Date.now();

        let idleIterations = 0;
        let previousCount = 0;

        if (await container.count()) {

            while (true) {

                if (Date.now() - startedAt > MAX_TIME_MS) {
                    break;
                }

                if (reviews.length >= MAX_REVIEWS) {
                    break;
                }

                if (loadedPages.size >= MAX_PAGES) {
                    break;
                }

                if (totalPages !== null && loadedPages.size >= totalPages) {
                    break;
                }

                await container.evaluate(
                    el => {
                        el.scrollTop = el.scrollHeight;
                    }
                );

                await page.waitForTimeout(500);

                if (reviews.length === previousCount) {
                    idleIterations++;
                } else {
                    previousCount = reviews.length;

                    idleIterations = 0;
                }

                if (idleIterations >= 5) {
                    break;
                }
            }
        }

        const result = {
            title,

            rating: ratingText ? Number(ratingText.replace(',','.')): null,

            reviews_count: reviewsCount,

            ratings_count: Number(ratingsCounterText.replace(/\D/g,'')) || 0,

            reviews
        };

        process.stdout.write(JSON.stringify(result));

    } catch (e) {
        console.error(e.stack);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();