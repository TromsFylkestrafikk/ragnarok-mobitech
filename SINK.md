# Sink Mobitech

Ferry ticketing and statistics for Boreal, Norled and Torghatten Nord.

## Data

The static table `mobitech_actors` holds the names of all operators
(e.g. Boreal) and is linked to the `actor_id` column in other tables.

Transaction data with tour ID, date, nationality and more is found in
the `mobitech_transactions` table. There's no price information here,
but the `operator_reference` column is a direct reference to Skyttel's
trip data and more details can be found over there.

Passenger count with related data like tour ID, departure time and
stop place IDs is stored in the `mobitech_statistics` table.

## Source

Mobitech provides transaction data from ticket sales (AutoPASS or
invoice) on ferry travels done in Troms municipality. Passenger count
for each tour is also included.

## Usage

Joining other tables will most likely be necessary. Columns named
`actor_id`, `tour_id` and `operator_reference` are good candidates.

The `tariff_class` column in the `mobitech_transactions` table will be
very useful if vehicle length is important. The AutoPASS codes (AP1-9)
in this column has the following meaning:

- AP1: < 6m
- AP2: 6 - 8m
- AP3: 8 - 10m
- AP4: 10 - 12m
- AP5: 12 - 14m
- AP6: 14 - 17.5m
- AP7: 17.5 - 19.5m
- AP8: 19.5 - 22m
- AP9: > 22m
- MC:  Motorcycle.