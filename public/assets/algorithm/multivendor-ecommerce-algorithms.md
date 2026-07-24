# Algorithms for Multivendor E-commerce Platform

This document explains three algorithms that can be implemented in a multivendor e-commerce system: **Weighted Rating (Bayesian Average)**, **Cosine Similarity**, and **TF-IDF**.
---

## 1. Weighted Rating (Bayesian Average)

### 1.1 Purpose

Used for **ranking products or vendors** by rating in a way that accounts for the *number* of reviews, not just the average score. This prevents a product with one 5-star review from outranking a product with thousands of consistently high reviews.

### 1.2 The Problem With a Simple Average

A naive system ranks products purely by:

$$
R = \frac{\sum \text{ratings}}{\text{number of ratings}}
$$

This is misleading when the number of ratings ($v$) is very small, since a handful of reviews can be biased, fake, or unrepresentative.

### 1.3 The Formula

$$
WR = \frac{v}{v+m} \cdot R \;+\; \frac{m}{v+m} \cdot C
$$

| Symbol | Meaning |
|:------:|---------|
| $WR$ | Final weighted rating (the score used for ranking) |
| $v$ | Number of reviews for this specific product |
| $R$ | Average rating of this specific product |
| $m$ | Minimum number of reviews required for full confidence (a chosen threshold) |
| $C$ | Mean rating across **all** products on the platform |

**How to interpret it:** The formula is a weighted blend of two things — what *this* product's own reviewers say ($R$), and what the *platform average* says ($C$). The weights $\frac{v}{v+m}$ and $\frac{m}{v+m}$ always sum to 1. As $v$ (review count) grows large, the first term dominates and the score approaches the product's true average. When $v$ is small, the score is pulled toward the safer platform average $C$.

### 1.4 Worked Example

Assume:
- Platform-wide average rating: $C = 3.8$
- Confidence threshold: $m = 10$

**Product X** — 2 reviews, average rating 5.0:

$$
WR_X = \frac{2}{2+10}(5.0) + \frac{10}{2+10}(3.8) = \frac{2}{12}(5.0) + \frac{10}{12}(3.8) = 0.833 + 3.167 = 4.00
$$

**Product Y** — 200 reviews, average rating 4.5:

$$
WR_Y = \frac{200}{200+10}(4.5) + \frac{10}{200+10}(3.8) = \frac{200}{210}(4.5) + \frac{10}{210}(3.8) = 4.286 + 0.181 = 4.47
$$

**Conclusion:** Product Y ranks higher (4.47) than Product X (4.00), despite Product X having a "perfect" rating — because Product X doesn't yet have enough reviews to be trusted.

### 1.5 Implementation Reference

```php
public function weightedRating(Product $product, float $C, int $m = 10): float
{
    $v = $product->reviews()->count();
    $R = $product->reviews()->avg('rating') ?? 0;

    return (($v / ($v + $m)) * $R) + (($m / ($v + $m)) * $C);
}
```

---

## 2. Cosine Similarity

### 2.1 Purpose

Used to build a **recommendation engine** ("Customers who bought this also bought...") by measuring how similar two users' purchase/browsing patterns are.

### 2.2 The Intuition

Each user is represented as a **vector** — a list of numbers describing their interaction with products (e.g., 1 if purchased, 0 if not). Cosine similarity measures the **angle** between two such vectors, not their length. Two users who buy the *same kinds* of products point in a similar direction, regardless of how *many* total purchases each has made.

### 2.3 The Formula

$$
\cos(\theta) = \frac{\mathbf{A} \cdot \mathbf{B}}{\|\mathbf{A}\| \, \|\mathbf{B}\|} = \frac{\displaystyle\sum_{i=1}^{n} A_i B_i}{\sqrt{\displaystyle\sum_{i=1}^{n} A_i^2} \; \cdot \; \sqrt{\displaystyle\sum_{i=1}^{n} B_i^2}}
$$

| Symbol | Meaning |
|:------:|---------|
| $\mathbf{A}, \mathbf{B}$ | Interaction vectors for User A and User B |
| $A_i, B_i$ | The $i$-th component of each vector (interaction with product $i$) |
| $\mathbf{A} \cdot \mathbf{B}$ | Dot product — sum of the products of matching components |
| $\|\mathbf{A}\|$ | Magnitude (length) of vector A |
| $\cos(\theta)$ | Result ranges from 0 (no similarity) to 1 (identical direction/preference) |

### 2.4 Worked Example

Consider 4 tracked products: **[Laptop, Mouse, Phone, Headphones]**.

- User A bought Laptop and Mouse: $\mathbf{A} = [1, 1, 0, 0]$
- User B bought Laptop and Headphones: $\mathbf{B} = [1, 0, 0, 1]$

**Step 1 — Dot product:**

$$
\mathbf{A} \cdot \mathbf{B} = (1)(1) + (1)(0) + (0)(0) + (0)(1) = 1
$$

**Step 2 — Magnitudes:**

$$
\|\mathbf{A}\| = \sqrt{1^2 + 1^2 + 0^2 + 0^2} = \sqrt{2} \approx 1.414
$$

$$
\|\mathbf{B}\| = \sqrt{1^2 + 0^2 + 0^2 + 1^2} = \sqrt{2} \approx 1.414
$$

**Step 3 — Cosine similarity:**

$$
\cos(\theta) = \frac{1}{1.414 \times 1.414} = \frac{1}{2} = 0.5
$$

**Conclusion:** A similarity score of 0.5 indicates moderate overlap (they share one product: Laptop). A score of 1.0 would mean identical purchase patterns; a score of 0 would mean no overlap at all.

### 2.5 Implementation Reference

```php
function cosineSimilarity(array $vectorA, array $vectorB): float
{
    $dotProduct = 0;
    $normA = 0;
    $normB = 0;

    foreach ($vectorA as $key => $value) {
        $dotProduct += $value * ($vectorB[$key] ?? 0);
        $normA += $value ** 2;
    }
    foreach ($vectorB as $value) {
        $normB += $value ** 2;
    }

    if ($normA == 0 || $normB == 0) return 0;

    return $dotProduct / (sqrt($normA) * sqrt($normB));
}
```

---

## 3. TF-IDF (Term Frequency – Inverse Document Frequency)

### 3.1 Purpose

Used to build a **smarter product search** that ranks results by relevance instead of a plain keyword match. TF-IDF scores a word higher when it appears often in one specific product's description, but rarely across the rest of the catalog.

### 3.2 Term Frequency (TF)

Measures how important a word is **within a single document** (product description).

$$
TF(t, d) = \frac{\text{number of times term } t \text{ appears in document } d}{\text{total number of terms in } d}
$$

### 3.3 Inverse Document Frequency (IDF)

Measures how **rare** a word is across the **entire catalog**. Rare words carry more distinguishing power.

$$
IDF(t) = \log\left(\frac{N}{1 + df(t)}\right)
$$

| Symbol | Meaning |
|:------:|---------|
| $N$ | Total number of products (documents) in the catalog |
| $df(t)$ | Number of products/documents that contain term $t$ at least once |
| $+1$ | Prevents division by zero when a term appears in no documents |
| $\log$ | Compresses the scale so extremely rare words don't dominate unreasonably |

### 3.4 Combined TF-IDF Score

$$
TFIDF(t, d) = TF(t, d) \times IDF(t)
$$

A search query's relevance to a product is the **sum** of the $TFIDF$ scores of all matching query terms.

### 3.5 Worked Example

Assume a catalog of $N = 100$ products. A user searches for **"wireless"**.

- Product X's description has 50 total words; "wireless" appears 3 times.
- The word "wireless" appears in 20 out of the 100 products ($df = 20$).

**Step 1 — Term Frequency:**

$$
TF = \frac{3}{50} = 0.06
$$

**Step 2 — Inverse Document Frequency:**

$$
IDF = \log\left(\frac{100}{1+20}\right) = \log\left(\frac{100}{21}\right) = \log(4.76) \approx 1.56
$$

**Step 3 — TF-IDF Score:**

$$
TFIDF = 0.06 \times 1.56 \approx 0.094
$$

**Comparison — a common word like "the":**

If "the" appears in all 100 products ($df = 100$):

$$
IDF_{the} = \log\left(\frac{100}{101}\right) = \log(0.99) \approx -0.01 \approx 0
$$

**Conclusion:** Common words like "the" score near zero and contribute almost nothing to relevance — TF-IDF naturally filters out unhelpful words without needing a manually maintained stop-word list.

### 3.6 Implementation Reference

```php
function tfidfScore(string $term, string $document, Collection $allDocuments): float
{
    $words = str_word_count(strtolower($document), 1);
    $termCount = count(array_filter($words, fn($w) => $w === strtolower($term)));
    $tf = $termCount / max(count($words), 1);

    $docsContainingTerm = $allDocuments->filter(
        fn($doc) => str_contains(strtolower($doc), strtolower($term))
    )->count();

    $idf = log(($allDocuments->count()) / (1 + $docsContainingTerm));

    return $tf * $idf;
}
```

---

## 4. Summary Table

| Algorithm | Solves | Core Formula | Output Range |
|---|---|---|---|
| Weighted Rating | Fair ranking despite uneven review counts | $WR = \frac{v}{v+m}R + \frac{m}{v+m}C$ | 0 – 5 (rating scale) |
| Cosine Similarity | Finding similar users for recommendations | $\cos(\theta) = \frac{\mathbf{A}\cdot\mathbf{B}}{\|\mathbf{A}\|\|\mathbf{B}\|}$ | 0 – 1 |
| TF-IDF | Relevance-ranked search | $TFIDF = TF \times IDF$ | Unbounded, higher = more relevant |

