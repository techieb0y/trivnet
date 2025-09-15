## External API

v1: 2025 Aug 30

# Read Ops

* URL: /api/v1/finish
* Method: `GET`
* Authenticatin: HTTP Basic auth
* Input: None

Returns a count of how many bib numbers have been reported as corssing the finish line:
```
{ "count": 7 }
```

* URL: /api/v1/finish
* Method: `GET`
* Authenticatin: HTTP Basic auth
* Input: `bibnum`

Returns whether the specified bib number has been reported as crossing the finish line:
```
{ "finished": "true" }
```
or
```
{ "finished": "false" }
```

# Write Ops

* URL: /api/v1/finish
* Method: `POST`
* Input: A list of bib numbers, one per line, of runners who have crossed the finish line.
* Authenticatin: HTTP Basic auth
* Send a `Content-Type` header of `text/csv`

Ideally, each batch should only include new finishers since the last submission, but it doesn't really hurt anything to send the full finisher list each time.
