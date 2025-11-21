SELECT s1.article, s1.dealer, s1.price
FROM shops s1
WHERE s1.price = (
    SELECT MAX(s2.price)
    FROM shops s2
    WHERE s2.article = s1.article
)
ORDER BY s1.article, s1.dealer;