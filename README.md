# Gift Exchange

Simple little gift exchange console command to email everyone's assignments and keep them secret.

See the `list.example.yaml` for how to configure the gift exchange. `exclusions` and `email` are optional,
but no email will be sent and the assignment will be unknown without it. If secrecy isn't important, you can
just run a dry-run and let people know. Names in exclusions must match the participant's name or the exclusion
won't match.

Run a test to show assignments to users:
```
$ bin/console app:exchange list.yaml --dry-run
Would send email to "John" assigned to "Bob".
Would send email to "Jane" assigned to "Rick".
Would send email to "Bob" assigned to "John".
Would send email to "Rick" assigned to "Jane".
```

Send actual assignments and emails:
```
$ bin/console app:exchange list.yaml
Sending email to "John"
Sending email to "Jane"
Sending email to "Bob"
Sending email to "Rick"
```
