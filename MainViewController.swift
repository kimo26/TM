//
//  MainViewController.swift
//  Financial Help
//
//  Created by Karim Galal on 21.12.19.
//  Copyright © 2019 Karim Galal. All rights reserved.
//
import Firebase
import FirebaseAuth
import FirebaseDatabase
import UIKit
import ProgressHUD
import WebKit
class MainViewController: UIViewController {
    var name = String()
    var ticker = String()
    var id = String()
    override func viewDidLoad() {
        super.viewDidLoad()
        chartView.backgroundColor = .clear
        loading.startAnimating()
        self.chartView.frame = self.view.frame
        getChart(ticker: ticker)
    }
    @IBOutlet weak var chartView: WKWebView!
    
    @IBAction func backing(_ sender: Any) {
        self.performSegue(withIdentifier: "back", sender: nil)
    }
    
    @IBOutlet weak var loading: UIActivityIndicatorView!
    var databaseRef: DatabaseReference!
    
    func getChart(ticker: String){
        databaseRef = Database.database().reference()
        guard let uid = Auth.auth().currentUser?.uid else {return}
        databaseRef.child("users").child(uid).observeSingleEvent(of: .value, with: {(snapshot) in
            let values = snapshot.value as! NSDictionary
            let period = values["strat"] as! String
            let html_name = self.name.replacingOccurrences(of: " ", with: "%20")
            if let url = URL(string:
                "https://finance-api.000webhostapp.com/sara.php?ticker="+ticker+"&period="+period+"&name="+html_name) {
                let request = URLRequest(url: url)
                self.chartView.navigationDelegate = self
                self.chartView.load(request)
            }
        })
        
    }

}
extension MainViewController: WKNavigationDelegate{
    func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
        self.loading.stopAnimating()
    }
}
