package com.example.easyvoteqrreader;

import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.support.v7.app.ActionBarActivity;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;

public class MainActivity extends ActionBarActivity {

	public final static String EXTRA_MESSAGE = "com.example.easyvoteqrreader.MESSAGE";
	static final String ACTION_SCAN = "com.google.zxing.client.android.SCAN";
	String contents = "";

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
	}

	// product qr code mode
	public void scanQR(View v) {
		try {
			// start the scanning activity from the
			// com.google.zxing.client.android.SCAN intent
			Intent intent = new Intent(ACTION_SCAN);
			intent.putExtra("SCAN_MODE", "QR_CODE_MODE");
			startActivityForResult(intent, 0);
		} catch (ActivityNotFoundException anfe) {
			// on catch, show the download dialog
			Uri marketUri = Uri
					.parse("market://details?id=com.secuso.privacyFriendlyCodeScanner");
			Intent marketIntent = new Intent(Intent.ACTION_VIEW, marketUri);
			startActivity(marketIntent);
		}
	}

	// on ActivityResult method
	public void onActivityResult(int requestCode, int resultCode, Intent intent) {
		if (requestCode == 0) {
			if (resultCode == RESULT_OK) {
				contents = intent.getStringExtra("SCAN_RESULT");

				//Wenn der eingegebene Code größer als 5 ist, wird getestet, welche Wahlart vorliegt. Diese wird danach aufgerufen.
				if (contents.length() >= 5) {
					Intent intent2 = null;
					int wahlart = (Integer) Integer.parseInt((String) contents
							.subSequence(0, 5));

					switch (wahlart) {
					case 54321:
						intent2 = new Intent(this, Kommunalwahl.class);
						break;
					case 88888:
						intent2 = new Intent(this, Europawahl.class);
						break;
					case 99999:
						intent2 = new Intent(this, Landtagswahl.class);
						break;
					default:		//wenn der Wahlcode nicht übereinstimmt, gibt die Application folgende Fehlermeldung aus
						TextView tv = new TextView(this);
						tv.setText("Der ausgelesene QR-Code\n"
								+ contents
								+ "\n ist kein zulässiger QR-Code für die Anwendung EasyVote.");
						setContentView(tv);
						break;
					}
					intent2.putExtra("test", contents);
					startActivity(intent2);
				} else {
					TextView tv = new TextView(this);
					tv.setText("Der ausgelesene QR-Code\n"
							+ contents
							+ "\n ist kein zulässiger QR-Code für die Anwendung EasyVote.");
					setContentView(tv);
				}
			}
		}
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.main, menu);
		return true;
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		// Handle action bar item clicks here. The action bar will
		// automatically handle clicks on the Home/Up button, so long
		// as you specify a parent activity in AndroidManifest.xml.
		int id = item.getItemId();
		if (id == R.id.action_settings) {
			return true;
		}
		return super.onOptionsItemSelected(item);
	}
}
