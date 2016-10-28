package com.example.easyvoteqrreader;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.ActionBarActivity;
import android.widget.TextView;

public abstract class Wahl extends ActionBarActivity{
	
	
	/**
	 * Diese Methode wird gestartet, sobald ein QR-String ausgelesen wurde. 
	 * Sie ruft die Methode showElection auf und gibt zum Schluss den zurückgegebenen String auf dem Bildschirm aus
	 */
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.ausgabe);
		
		Intent intent = getIntent();
		String contents = intent.getStringExtra("test");

		String wahl = showElection(contents);
	
		TextView tv = (TextView) findViewById(R.id.textView2);		
		if(tv != null){
			tv.setTextSize(17);
			tv.setText(wahl);	
		}
	}

	/**
	 * Methode die einen Wahlcode übergeben bekommt und aus diesem die Wahl des
	 * Anwenders rekonstruiert
	 * 
	 * @param contents
	 * 		Der ausgelesene QR-Code als Wahlcode
	 * @return
	 * 		Der rekonstruierte Wahlcode als String
	 */
	public abstract String showElection(String contents);
	
}
