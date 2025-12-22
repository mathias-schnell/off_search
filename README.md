# off_search

### What this tool is

This is a CLI tool that uses the Open Food Facts API to search for and display food product information. The tool is intentionally CLI-only to focus on backend data retrieval and processing.


### What this tool is not

This tool operates only on singular products and does not perform complex filtering or analysis across multiple products. Dataset-wide filtering is intentionally out of scope for the initial version.


### List of commands

off_search query "<query string>" - Returns a list of five food products closest to the query string with their barcode numbers.

```text
Results for "chocolate milk":

1) Fairlife Chocolate Milk — 737628064502
2) Hershey's Chocolate Milk — 034000191002
3) Organic Valley Chocolate Milk — 093966000345
4) Nesquik Chocolate Milk — 028000000123
5) Horizon Chocolate Milk — 036632000456
```

off_search info "<barcode number>" - Return available nutritional and ingredient information for the food product that matches the given barcode.

```text
Product: Fairlife Chocolate Milk
Brand: Fairlife

Ingredients:
- milk
- sugar
- cocoa
- salt
- soy lecithin

Nutrition (per serving):
- calories: 150 kcal
- fat: 4 g
- carbohydrates: 13 g
- sugar: 12 g
- protein: 13 g
```


### What errors look like

When information on a product is incomplete...

```text
Product: Fairlife Chocolate Milk
Brand: N/A

Ingredients: N/A

Nutrition (per serving): N/A
```

When a search finds no products...

```bash
off_search query "golden ticket"
```

```text
Error: We're sorry, no products with that name could be found.
```

When an invalid barcode is used...

```bash
off_search info 001234567890
```

```text
Error: We're sorry, we could not find any information for this barcode number.
```

When the tool can't connect to the API...

```bash
off_search query "toblerone"
```

```text
Error: Can't connect to Open Food Facts API at this time. Please try again later.
```

### Plans for future updates

None at this time.