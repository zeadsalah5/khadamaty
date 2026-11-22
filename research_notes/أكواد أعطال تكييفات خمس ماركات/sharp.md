# Sharp (Sharp Al Araby / Elaraby) Split AC: Error Codes and Common Faults, Egypt

Confidence key: **High** = Sharp official page or a Sharp service/operation manual. **Medium** = a third-party copy of a Sharp document. **Low** = an aggregator or marketing site with no model attribution. Low items are not suitable for articles without checking them against the manual.

## 1. How Sharp splits display faults, and which model families are sold in Egypt

### Takeaway
Sharp splits report faults by blinking the OPERATION (red) and TIMER (orange) lamps on the indoor unit. Newer models with a digital display also show a two-part numeric code such as "11-2". Self-diagnosis memory is recalled while the unit is stopped, by holding the AUX or emergency button for at least 5 seconds. Removing power erases that memory. Egyptian retail is dominated by digital Plasmacluster lines, both inverter and non-inverter (AH-XP / AY-XP inverter, AH-AP "Premium Plus" non-inverter). I found no Egypt-specific code table.

### Cited Findings
- The owner manual says to call for service when the OPERATION and TIMER lamps on the unit blink (AH-XPC12RV; the AH-XP18MV owner manual is quoted the same way, naming the lamps red and orange). High. — [AH-XPC12RV manual (via search snippet)](https://manualshive.com/en/manual/sharp/ah-xpc12rv/365034?page=14); [techenclave thread](https://techenclave.com/goto/post?id=1834704)
- Service manuals: in normal operation only the TIMER lamp blinks, and an error shows as blinking of the run/operation lamp. If the TIMER lamp blinks during operation, the table is used to diagnose it (AH-X10). High (via snippet). — [Sharp AH-X10 service manual](https://servlib.com/sharp/air-conditioner/ah-x10-sm9.html)
- To run the diagnosis, press "AUX." for 5 seconds or more while the unit is stopped. Combined blinking of the TIMER and OPERATION lamps maps to a table (AE-XM18CR / AE-XM24CR). High (via snippet). — [AE-XM18CR SM](https://servlib.com/sharp/air-conditioner/ae-xm18cr-sm11.html); [AE-XM24CR SM](https://servlib.com/sharp/air-conditioner/ae-xm24cr-sm10.html)
- The blink tables use timing legends such as "N blinks at 1 s intervals" or "3 times at 0.2 s". The timing differs by model. Medium. — [servlib AY-XP08CR SM](https://servlib.com/sharp/air-conditioner/ay-xp08cr-sm14.html?start=28)
- Cutting power (unplugging or turning off the breaker) erases the self-diagnostic memory (AH-X13). Record the pattern before cutting power. High (via snippet). — [AH-X13 SM](https://servlib.com/sharp/air-conditioner/ah-x13-sm9.html)
- AY-XP09ER: hold the emergency button for more than 5 s while the unit is stopped. The seven-segment display alternates the main and sub category every second (for example "21" then "-0"). The outdoor unit's LED1 flashes the code for about 3 to 10 minutes after a fault and cannot recall stored history. Medium (blog reproducing a Sharp SM). — [masterelectronicsrepair blog](https://masterelectronicsrepair.blogspot.com/2019/03/sharp-xp09er-split-air-conditioner-led.html?m=1)
- Sharp's official first steps: unplug for more than 1 minute, clean the air filter, clear obstructions from the indoor and outdoor units and debris from the outdoor fins, then restart. If the code persists, call service and give them the code. High. — [Sharp global error code list](https://global.sharp/smartapp/air/support/airconerror/)
- Egyptian models on sale: AY-XP12UHE (1.5 HP cool/heat inverter, digital, Plasmacluster; Elaraby lists a 5-year warranty), AH-XP12BME (cool-only inverter, "J-Tech Inverter", Plasmacluster, self clean), AH-AP12YHE / AH-AP18UHE / AH-AP24UHE ("Premium Plus" digital Plasmacluster, cool only, non-inverter), AY-XP12YHE (indoor unit made in Thailand, outdoor unit made in Egypt), AY-XP18BHE, AY-XP24UHE / AY-XP24YHEB. — [Elaraby AY-XP12UHE](https://www.elarabygroup.com/en/sharp-split-air-conditioner-1-5hp-cool-heat-inverter-digital-plasmacluster-silver-color-ay-xp12uhe) (returned 404 at fetch time; seen in search); [Carrefour Egypt AH-XP12BME](https://www.carrefouregypt.com/mafegy/en/air-conditioners/sharp-split-air-conditioner-1-5-hp-cooling-only-inverter-white-ah-xp12bme/p/1698542000868); [Elaraby AH-AP18UHE](https://www.elarabygroup.com/en/sharp-split-air-conditioner-2-25hp-cool-premium-plus-digital-with-plasmacluster-in-white-color-ah-ap18uhe); [Elaraby AH-AP24UHE](https://www.elarabygroup.com/en/sharp-split-air-conditioner-3hp-cool-premium-plus-digital-with-plasmacluster-in-white-color-ah-ap24uhe); [ehabcenter AY-XP12YHE](https://ehabcenter.com/product/sharp-split-air-conditioner-inverter-1-5-hp-cool-and-heat-plasma-cluster-digital-white/); [Jumia AY-XP18BHE](https://www.jumia.com.eg/sharp-split-air-conditioner-2.25-hp-cool-heat-inverter-digital-plasma-cluster-white-ay-xp18bhe-134047235.html)

### Inferences
- Model prefixes: AH = cooling only, AY = cool/heat. XP = inverter (Elaraby and retailer listings label AH-XP and AY-XP as inverter). AP = non-inverter "Premium Plus". This prefix scheme is inferred from the listings and is not an official Sharp decoding.
- Articles should say "the blink count meaning depends on your model's service manual" and give the safe universal step: note the pattern, power-cycle, then call service.

### Gaps
- I could not open the actual blink-count to fault table for any Egyptian model. The servlib pages render as images or empty text, and the manualshive and manualmachine pages blocked the fetch.
- I found no Elaraby-published error code list for Egypt, and no confirmation of which Egyptian models show numeric "X-Y" codes on the display.

## 2. Most common fault codes and their meanings

### Takeaway
The only authoritative list I found is Sharp's official hyphenated "main-sub" code list. Sharp does not tie it to specific models, and the support contacts it gives are for Asian countries, not Egypt. Letter codes (E1, F0, H6, ...) on third-party sites look like another manufacturer's table and should not be published as Sharp codes.

### Cited Findings (all from the [Sharp official list](https://global.sharp/smartapp/air/support/airconerror/), confidence High for the meanings and Medium for applicability to Egyptian models)
- 1-x: thermistor short circuit (1-0 heat exchanger, 1-1 outdoor temperature, 1-2 suction, 1-3 two-way valve, 1-4 heat sink).
- 5-x: thermistor open circuit (5-0 heat exchanger, 5-1 outdoor temperature, 5-2 suction, 5-3 two-way valve, 5-4 discharge, 5-5 heat sink).
- 2-x: overheating (2-0 compressor, 2-1 compressor discharge, 2-2 outdoor heat exchanger, 2-3 indoor heat exchanger, 2-4 and 2-5 IPM high temperature).
- 3-0: temporary stop during dehumidify operation.
- 6-0: DC overcurrent. 6-1: IPM pin level error.
- 7-0: AC overcurrent. 7-1: AC current detected while off. 7-2: AC current above maximum. 7-3: AC current too low.
- 9-0: thermistor installation error or 4-way valve error. 9-3: compressor torque control. 9-4: 4-way valve error or **gas leak**. 9-5: **2-way and 3-way valves closed** (an installation error). 9-6: solenoid valve.
- 10-0, 10-1, 10-2: outdoor EEPROM / CPU RAM data error.
- 11-0 to 11-5: outdoor DC fan (rotation error, driver IC, **locked**, reverse rotation detected before the compressor starts, inverter current detection, connector open).
- 12-0: thermal fuse blown in the power terminal board.
- 13-0 to 13-3: compressor startup / rotation errors, inverter current detection.
- 14-0: PAM overvoltage. 14-1: PAM clock error. 14-2: PAM undervoltage. 14-4: PFC module error.
- 17-0: serial (indoor/outdoor communication) open circuit. 18-0: serial short circuit. 18-1: serial wiring error. 18-2: high-speed serial error.
- 19-0 to 19-3: indoor fan error (low rotation, rotating while stopped, high rotation).
- 20-0 to 20-3: indoor EEPROM errors.
- 21-0: louver cover or louver lever not fully closed.
- 22-0: Plasmacluster connection error. 22-1: lock-on sensor error. 22-2: Plasmacluster accumulated operation time exceeded.
- 23-0: incorrect connection to 200 V AC. 23-1: incorrect voltage during operation.
- 24-0 to 24-3: Wi-Fi errors (module, router, internet, server).
- 25-1: AC clock error. 26-1 / 26-2: indoor room / pipe thermistor errors. 26-3 / 26-4: gas / dust sensor errors.
- 28-x, 29-x: filter-cleaning and panel errors (these apply only to models with an auto-clean panel). 31-x: sub-microcomputer error.
- A third-party "120 Sharp codes" page lists letter codes: E1 high pressure, E5 overcurrent, E6 communication, F1 to F5 sensors, H6 indoor fan, PH / PL DC bus voltage, LP mismatch, and others. Low. This set matches another OEM's (Gree-style) table almost exactly, and the page names no Sharp models. — [arlingtonairconditioningheating.com](https://arlingtonairconditioningheating.com/sharp-air-conditioner-error-codes/); similarly [acerrorcode.com](https://acerrorcode.com/?p=403)
- H1 appears as "indoor EEPROM error" on one aggregator and E1 as "high pressure" on others. Other brands use E1 for communication errors. — [techfaultfix](https://techfaultfix.com/air-conditioners/sharp)

### Inferences
- Codes that suit consumer articles, from the official list: 9-5 (valves closed after installation), 9-4 (possible gas leak), 11-2 (outdoor fan locked), 17-0 / 18-x (indoor/outdoor wiring or communication), 23-x (wrong supply voltage, relevant to Egyptian voltage fluctuations), 21-0 (louver not closed), 22-x (Plasmacluster).

### Gaps
- I could not confirm that Egyptian AH-XP / AY-XP / AH-AP units use this numeric list, or what their blink counts are. Do not publish letter codes as Sharp codes.

## 3. Plasmacluster indicator vs cooling faults

### Takeaway
The blue Plasmacluster lamp is a normal status light that turns on when the feature is switched on from the remote. Faults are signalled by the OPERATION and TIMER lamps instead. Plasmacluster-specific faults exist in the official list as 22-x.

### Cited Findings
- The Plasmacluster lamp lights when the function is activated from the remote. I found no manual entry describing it blinking as a fault. Medium (search summary of the AH-XPC12RV manual). — [AH-XPC12RV manual](https://manualshive.com/en/manual/sharp/ah-xpc12rv/365034?page=14)
- 22-0: Plasmacluster connection error. 22-2: Plasmacluster accumulated operation time exceeded (suggests the ion generator unit has reached its service life). High. — [Sharp official list](https://global.sharp/smartapp/air/support/airconerror/)

### Inferences
- If the Plasmacluster lamp is off but the unit cools, that is not a cooling fault. The Plasmacluster function is separate from the refrigeration circuit.

### Gaps
- I found no sourced meaning for a blinking Plasmacluster lamp, and no sourced service interval for replacing the ion generator.

## 4. Common faults that show no code

### Takeaway
I could not retrieve a Sharp "Before calling for service" table (the PDFs were over 10 MB, and the manual mirrors blocked the fetch). Only partial checks are sourced. Generic causes need another brand's or a general sourced reference before they are published.

### Cited Findings
- Pre-service checks: whether the breaker has tripped or the fuse has blown, and whether the remote batteries are installed properly. High (manual snippet). — [AH-XPC12RV manual](https://manualshive.com/en/manual/sharp/ah-xpc12rv/365034?page=14)
- Weak cooling or a code: clean the filter, clear obstructions to indoor and outdoor airflow, and clean the outdoor fins. High. — [Sharp official](https://global.sharp/smartapp/air/support/airconerror/)
- Louver not closing has its own code, 21-0. A gas leak can appear as 9-4. Closed service valves appear as 9-5. Supply voltage problems appear as 23-x and 14-x. High. — [Sharp official](https://global.sharp/smartapp/air/support/airconerror/)
- Field report on an AH-XP18MV: compressor not running, only the fan working, with OPERATION and TIMER lamps blinking. Low (forum, no resolution). — [techenclave](https://techenclave.com/goto/post?id=1834704)

### Gaps
- The causes of water leaks, noise, and smell, and normal phenomena such as the 3-minute compressor restart delay, are not sourced here for Sharp. The operation manual PDFs that should contain them are [OM_ZU_ZHU_BU.pdf](https://global.sharp/smartapp/air/support/airconmanual/OM_ZU_ZHU_BU.pdf) and [OM_CPU.pdf](https://global.sharp/smartapp/air/support/airconmanual/OM_CPU.pdf). Both were too large to fetch; download them manually to fill this gap.
