# Unionaire (يونيون إير) AC error codes and common faults (Egypt)

## Q1: Display vs LED blinks, product lines, OEM platform

### Takeaway
Unionaire is an Egyptian manufacturer (founded 1995). Current lines sold in Egypt include the "Artify" inverter split and standard (fixed-speed) splits. I found no public source naming an OEM or platform partner, and no official source on whether a given model shows codes on a display or by LED blinks. One unofficial source describes LED-blink combinations alongside E-codes.

### Cited Findings
- Unionaire was founded in 1995 in Egypt. It calls itself "the first company in the Middle East to produce split high-wall air conditioning." The page names no technology partners — [unionaire.com](https://unionaire.com/?p=31187)
- Unionaire Group runs more than 15 factories, mainly in 6th of October City — [Devex profile](https://www.devex.com/organizations/unionaire-group-121826) (search snippet; not fetched)
- Retailer B.Tech sells these Artify inverter split models: INVARTIFY-015-CR (1.75 HP, cooling only), INV-ARTO012 (1.5 HP) and INVARTIFY-020-CR (2.75 HP). The listings show R32 refrigerant and a 5-year warranty — [B.Tech Artify 1.75HP](https://btech.com/en/p/unionaire-artify-inverter-split-air-conditioner-1-75-horsepower-inverter-motor-only-cooling-white-invartify-015-cr); [B.Tech Artify 1.5HP](https://btech.com/ar/p/unionaire-artify-invrtr-ac-1-5-art12hvuv)
- One unofficial site pairs each code with an LED pattern, e.g. E1 = flashing Power, E2 = Power + Sleep. This suggests at least some units, probably older ones without a digit display, signal faults by indicator LEDs — [acerrorcode.com](https://acerrorcode.com/articles/unionaire-ac-error-codes.html). Confidence: LOW to MEDIUM.

### Inferences
- The code style (E1 to E5 sensors and compressor, P1 voltage) resembles generic Chinese-controller tables such as Chigo, Gree or AUX. Without a source this is only a guess (LOW confidence). Do not name an OEM in articles.

### Gaps
- No official Unionaire user or service manual with an error table was found online: no hits on unionaire.com, ManualsLib or Manuals+.
- No source confirms whether Artify or other inverter models use a digital display, or whether their code table differs from the non-inverter one.
- No source says whether window units have fault codes. Most are mechanical or have a simple control board.

## Q2: Known error codes and meanings

### Takeaway
The only Unionaire-specific code list found is unofficial: E1 to E5 plus P1 and P2. It is not model-specific and was not confirmed by Unionaire. Present the codes as "common meanings reported by references; check your manual or service centre" (LOW to MEDIUM confidence).

### Cited Findings
All entries come from [acerrorcode.com – Unionaire AC error codes](https://acerrorcode.com/articles/unionaire-ac-error-codes.html), an unofficial aggregator. The page claims "16+ codes" but lists only 7.

| Code | Meaning (per source) | LED pattern (per source) | Confidence |
|---|---|---|---|
| E1 | Room temperature sensor missing or faulty | Power flashing | LOW–MED |
| E2 | Indoor coil sensor missing or faulty | Power + Sleep flashing | LOW–MED |
| E3 | Outdoor sensor missing or faulty | Power flashing | LOW–MED |
| E4 | Compressor overload | Power + Cool flashing | LOW–MED |
| E5 | Insufficient cooling or heating (wrong voltage, gas leak, indoor sensor) | Power + Cool flashing | LOW–MED |
| P1 | Over-voltage or under-voltage protection | not stated | LOW–MED |
| P2 | Compressor-top (discharge) over-temperature protection | not stated | LOW–MED |

- The same E1, E5 and P1 meanings appear on blogspot copies, which look like copies of the same content and are not independent corroboration — [coolmasternpt.blogspot](https://coolmasternpt.blogspot.com/2021/09/unionaire-air-conditioner-error-codes.html); [coolrival.blogspot](https://coolrival.blogspot.com/2021/09/unionaire-air-conditioner-error-codes_25.html)
- Other brands use the same codes for different faults. One example is Comfortaire, where E1 means high-pressure protection — [acerrorcode Comfortaire](https://acerrorcode.com/comfortaire-mini-split-ductless-ac-error-codes.html/comment-page-1). Do not borrow other brands' tables.

### Inferences
- E1 to E3 point to thermistor faults, E4 and P2 to compressor protection, and E5 and P1 to supply voltage or refrigerant. This matches typical controller logic, but which unit it applies to is unknown.

### Gaps
- No official confirmation exists for any code. No inverter-specific codes (IPM, PFC, communication) were found for Artify.
- The E5 "fix" section on the source page describes normal behaviours (3-minute restart delay, defrost) rather than an E5 repair.

## Q3: Common non-coded faults (older split and window units)

### Takeaway
I found no Unionaire-specific fault statistics. Generic manufacturer guidance (LG) covers water leaks and poor cooling. Faults such as capacitors, contactors and fan motors are standard for fixed-speed units but are not sourced specifically for Unionaire.

### Cited Findings
- A clogged air filter can cause frost on the indoor coil, which then leaks water. Clean filters about every 2 weeks — [LG CA](https://www.lg.com/ca_en/support/product-support/troubleshoot/help-library/cs-CT52006110-1433991512898/)
- Condensate collects in the drain pan and leaves through the drain line. A blocked or poorly sloped pan or line causes water inside the room. Some droplets in high humidity are normal — [LG US](https://www.lg.com/us/support/help-library/lg-air-conditioner-why-is-water-dripping-or-condensation-forming-on-the-air-conditioner--20155363574975)
- A unit that does not restart for about 3 minutes after being switched off is normal protection, not a fault (noted on the Unionaire code page) — [acerrorcode.com](https://acerrorcode.com/articles/unionaire-ac-error-codes.html)
- Low voltage and refrigerant leaks are listed causes of poor cooling (E5 and P1 context) — [acerrorcode.com](https://acerrorcode.com/articles/unionaire-ac-error-codes.html)

### Inferences (general HVAC knowledge, not Unionaire-sourced; MEDIUM confidence as generic)
- Old fixed-speed splits and window units: a weak or failed run capacitor (compressor or fan does not start, hums), contactor pitting in the outdoor unit, fan motor bearing wear, a dirty condenser, and gas loss at flare joints.
- Window units: water inside the room usually comes from the unit tilting toward the room instead of slightly toward the outside, or from a blocked drain hole or pan.
- Voltage fluctuation is common in Egypt. Writers should advise a voltage protector.

### Gaps
- No Arabic forum or technician source specific to Unionaire was found to corroborate frequency of these faults.
- No Unionaire service-centre guidance on window-unit faults was found.
