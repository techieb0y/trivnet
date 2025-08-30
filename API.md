## External API

v1: 2025 Aug 30

* URL: /api/v1/finish
* Method: `POST`
* Input: A list of bib numbers, one per line, of runners who have crossed the finish line.
* Authenticatin: HTTP Basic auth
* Send a `Content-Type` header of `text/csv`

Ideally, each batch should only include new finishers since the last submission, but it doesn't really hurt anything to send the full finisher list each time.
