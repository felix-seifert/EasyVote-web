package com.example.easyvoteqrreader;

import java.io.IOException;

import org.xmlpull.v1.XmlPullParser;
import org.xmlpull.v1.XmlPullParserException;
import org.xmlpull.v1.XmlPullParserFactory;

public class Europawahl extends Wahl {

	/**
	 * Methode die einen Wahlcode übergeben bekommt und aus diesem die Wahl des
	 * Anwenders rekonstruiert
	 */
	@Override
	public String showElection(String contents) {
		/*
		 * Die folgenden Werte werden aus de, Wahlcode ausgelesen
		 * 
		 * wahlart: Die ausgelesene Wahlart (hier Kommunalwahl) 
		 * gueltig: Ob die Wahl gütlig oder ungürlig ist 
		 * partei_id: Die ID der gewählten Partei
		 */
		String wahlart = (String) contents.subSequence(0, 5);

		String gueltig = (String) contents.subSequence(5, 6);
		if (gueltig.equals("1")) {
			gueltig = "Gültig";
		} else {
			gueltig = "Ungültig";
		}

		String partei_id = (((Integer) Integer.parseInt((String) contents
				.subSequence(6, 8))).toString());

		/*
		 * Im nachfolgenden Block wird das zur Wahl gehörige XML-Dokument
		 * geöffnet und die Wahlart, sowie der Parteiname ausgelesen
		 */
		String tmp = "";
		String partei_name = "-";
		String wahljahr = "";
		boolean right_party = false;
		XmlPullParserFactory factory;
		try {
			factory = XmlPullParserFactory.newInstance();
			factory.setNamespaceAware(true);
			XmlPullParser xpp = factory.newPullParser();

			xpp.setInput(this.getAssets().open(wahlart + ".xml"), null);
			int eventType = xpp.getEventType();

			while (eventType != XmlPullParser.END_DOCUMENT) {
				String name = xpp.getName();
				switch (eventType) {
				case XmlPullParser.START_TAG:
					if (name.equals("party")) {
						if (partei_id.contains(xpp
								.getAttributeValue(null, "id"))) {
							right_party = true;
						}
					}
					break;
				case XmlPullParser.TEXT:
					tmp = xpp.getText();
					break;

				case XmlPullParser.END_TAG:
					if (name.equals("election_name")) {
						wahlart = tmp;
					}
					if (name.equals("election_year")) {
						wahljahr = tmp;
					}
					if (name.equals("party") && right_party) {
						partei_name = tmp;
						right_party = false;
					}

					else {
					}
					break;
				}
				eventType = xpp.next();
			}
		} catch (XmlPullParserException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}

		return wahlart + " (" + wahljahr + ")\n\nStatus: " + gueltig
				+ "\n\nGewählte Partei: " + partei_name;
	}

}
