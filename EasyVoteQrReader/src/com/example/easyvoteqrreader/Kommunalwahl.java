package com.example.easyvoteqrreader;

import java.io.IOException;
import java.util.ArrayList;

import org.xmlpull.v1.XmlPullParser;
import org.xmlpull.v1.XmlPullParserException;
import org.xmlpull.v1.XmlPullParserFactory;


public class Kommunalwahl extends Wahl {

	/**
	 * Methode die einen Wahlcode übergeben bekommt und aus diesem die Wahl des Anwenders rekonstruiert
	 */
	public String showElection(String contents) {
		/*
		 * Die folgenden Werte werden aus de, Wahlcode ausgelesen
		 * 
		 * wahlart: Die ausgelesene Wahlart (hier Kommunalwahl)
	 * gueltig: Ob die Wahl gütlig oder ungürlig ist
	 * partei_id: Die ID der gewählten Partei
	 * mitglieder: Die IDs der gewählten Mitglieder
	 * deleted_members: Die IDs der gelöschten Mitglieder
		 */
		String wahlart = (String) contents.subSequence(0, 5);

		String gueltig = (String) contents.subSequence(5, 6);
		if (gueltig.equals("1")) {
			gueltig = "Gültig";
		} else {
			gueltig = "Ungültig";
		}
		String[] tmp2 = contents.split("_");
		int string_length = tmp2[0].length();

		String partei_id = (((Integer) Integer.parseInt((String) contents
				.subSequence(6, 8))).toString());

		int number_of_members = (string_length - 8) / 5;		
		String[][] mitglieder = new String[number_of_members][2];
		int initial = 8;
		for (int i = 0; i < number_of_members; i++) {
			mitglieder[i][0] = (((Integer) Integer.parseInt((String) tmp2[0]
					.subSequence(initial, initial + 4))).toString());
			mitglieder[i][1] = ((String) tmp2[0].subSequence(initial + 4, initial + 5));
			initial = initial + 5;
		}
		

		int deleted_length = 0;
		if (tmp2.length > 1) {
			deleted_length = tmp2[1].length();
		}
		int number_of_deleted_members = deleted_length / 4;
		ArrayList<String> deleted_members = new ArrayList<String>();
		initial = 0;
		for (int i = 0; i < number_of_deleted_members; i++) {
			deleted_members.add(((Integer) Integer.parseInt((String) tmp2[1]
					.subSequence(initial, initial + 4))).toString());
			initial = initial + 4;
		}

		/*
		 * Im nachfolgenden Block wird das zur Wahl gehörige XML-Dokument geöffnet und die Namen, die zu den berechneten IDs gehören, ausgelesen
		 */
		String tmp = "";
		String wahljahr = "";
		String partei_name = "-";
		int i = 0;
		String[][] mitglieder_names = new String[number_of_members][2];
		ArrayList<String> deleted_names = new ArrayList<String>();
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
						if (partei_id.equals(xpp
								.getAttributeValue(null, "id"))) {
							right_party = true;
						}
					}
					if (name.equals("candidate")) {
						if(inArray(xpp.getAttributeValue(null,
								"id"), mitglieder)){
							mitglieder_names[i][0] = xpp.getAttributeValue(null,
									"id")
									+ " "
									+ xpp.getAttributeValue(null, "name")
									+ ", "
									+ xpp.getAttributeValue(null, "prename")
									+ " ("
									+ xpp.getAttributeValue(null, "partei")
									+ ")";
							String id = xpp.getAttributeValue(null,"id");
							int x = getIndexOfValue(id, mitglieder);
							mitglieder_names[i][1] = mitglieder[x][1];
							i++;
							
						}
						else if (deleted_members.contains(xpp
								.getAttributeValue(null, "id"))) {
							deleted_names.add(xpp.getAttributeValue(null, "id")
									+ " " + xpp.getAttributeValue(null, "name")
									+ ", "
									+ xpp.getAttributeValue(null, "prename")
									+ " ("
									+ xpp.getAttributeValue(null, "partei")
									+ ")");
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
					
					break;
				}
				eventType = xpp.next();
			}
		} catch (XmlPullParserException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}

		/*
		 * Zum Schluss wird aus den erstellten Arrays ein String erstellt, der schließlich zurückgegeben wird
		 */
		String gewaehlt = "";		
		for (String[] mitglied : mitglieder_names) {
			gewaehlt = gewaehlt + mitglied[0] + " / "
					+ mitglied[1] + "\n";
		}

		String deleted = "";
		for (String mitglied : deleted_names) {
			deleted = deleted + mitglied + "\n";
		}

		if (gewaehlt.length() == 0) {
			gewaehlt = "-";
		}
		if (deleted.length() == 0) {
			deleted = "-";
		}

		return wahlart + " (" + wahljahr +")\n\n" + "Status: " + gueltig + "\n\nGewählte Partei: " + partei_name
				+ "\n\nDirekt gewählte Kandidaten / Stimmen: \n" + gewaehlt
				+ "\nGestrichene Kandidaten:\n" + deleted;

	}
	
	/**
	 * Gibt den Index zurück, an dem sich die ID eines Mitgliedes befindet
	 * 
	 * @param id
	 * 		Die ID eines Mitgliedes
	 * @param array_name
	 * 		Ein Array, das die ID und die Stimmenanzahl der Mitglieder enthält
	 * @return
	 * 		Es wird der Index zurückgegeben, an dem sich die ID des Mitgliedes im Array befindet
	 */
	private int getIndexOfValue(String id, String[][] array_name) {
		for(int i = 0; i < array_name.length; i++){
			if(id.equals(array_name[i][0])){
				return i;
			}
		}
		return -1;
	}

	/**
	 * Testet, ob sich ein String in einem Array befindet
	 * 
	 * @param value	
	 * 		String, der überprüft werden soll
	 * @param array_name
	 * 		Array, in dem sich der String befinden soll
	 * @return
	 * 		true, wenn sich der String im Array befindet, sonst false
	 */
	public boolean inArray(String value, String[][] array_name){		
		for(String[] name : array_name){
			if(value.equals(name[0])){
				return true;
			}
		}
		return false;
	}

}
