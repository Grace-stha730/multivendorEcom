# TF-IDF and cosine similarity: worked example

`RecommendationService` uses a sparse vector: only terms with a non-zero TF-IDF
weight are stored in `product_vectors.vector`. This is both smaller than a dense
vocabulary-sized array and mathematically identical for cosine similarity.

```php
// TF(t, d) = occurrences of t in d / total terms in d
$tf = $count / $termTotal;

// IDF(t) = log(N / (1 + documentsContainingTerm))
$idf = log($totalDocuments / (1 + $documentFrequency[$term]));

// TF-IDF(t, d) = TF(t, d) * IDF(t)
$vector[$term] = $tf * $idf;
```

The actual cosine implementation is:

```php
$dot = 0.0;
$normA = 0.0;
$normB = 0.0;

foreach ($vectorA as $term => $weight) {
    $dot += $weight * ($vectorB[$term] ?? 0);
    $normA += $weight ** 2;
}
foreach ($vectorB as $weight) {
    $normB += $weight ** 2;
}

$similarity = $normA > 0 && $normB > 0
    ? $dot / (sqrt($normA) * sqrt($normB))
    : 0.0;
```

## Three product documents

After lowercase conversion, punctuation removal, and stopword removal, suppose
the catalogue contains these three documents:

| Product | Clean document |
| --- | --- |
| A | `wireless headphones noise cancelling` |
| B | `wireless headphones travel audio` |
| C | `office chair ergonomic support` |

For `noise`, `cancelling`, `travel`, `audio`, and each term in C, `n_t = 1` and
`IDF = log(3 / (1 + 1)) = 0.405465`. Each occurs once in a four-term document,
so its TF-IDF weight is `1/4 * 0.405465 = 0.101366`.

`wireless` and `headphones` appear in two documents, so the requested formula
gives `IDF = log(3 / (1 + 2)) = 0`; they correctly contribute no discriminating
weight in this tiny corpus. Therefore the sparse vectors are:

```text
A = { noise: 0.101366, cancelling: 0.101366 }
B = { travel: 0.101366, audio: 0.101366 }
C = { office: 0.101366, chair: 0.101366, ergonomic: 0.101366, support: 0.101366 }
```

`cosine(A, B) = 0`: they have no **discriminating** terms in common. The result
is expected with exactly three documents and the specified IDF equation. In a
real catalogue, where `N` is much larger than 3, a term occurring in just two
products has positive IDF; for example, with `N = 100`, its IDF is
`log(100 / 3) = 3.506558`, so the common `wireless` and `headphones` terms make
A and B score well above C. This is why vectors are rebuilt against the entire
catalogue rather than only the two or three products being compared.

The persisted vectors are rebuilt with:

```bash
php artisan recommendations:rebuild-vectors
```

and product detail pages only load those stored vectors and calculate cosine
scores; they do not rebuild the catalogue-wide TF-IDF model during a request.
