# EasyVote
EasyVote is the concept of an hybrid (electronic/paper) voting system that has been developed within the project "Constitutional Compliant Electronic Voting" (Verfassungskonforme Umsetzung von elektronischen Wahlen ) which was funded by the "DFG - Deutsche Forschungsgemeinschaft". This project contains a web-based implementation of EasyVote.

## Motivation
Elections vary greatly from country to country, but also within eachcountry for different types of elections. Some have very simple ballotswith two candidates or just a yes or no question, while other ballotslike for local elections in Germany contain more than 500 candidates,allow for more than 70 votes and the system also allows voters toperform cumulative voting, vote splitting and crossing out of candidateswhich results in huge ballot papers (in Darmstadt in 2006 localelections about 27” x 35”). Manually tallying is very likely to be errorprone and time intensive. The tallying for the local elections inGermany usually takes between four to six days. They have computersupport for the counting and tallying where they enter vote by vote.Also vote casting is error prone as voters might accidently and withoutrealizing spoil the vote. To improve the current situation the EasyVoteconcept has been proposed by Volkamer et. al. This concept mainlyaddresses challenges in elections with complicated paper ballots, butit is applicable to any type of election. EasyVote is the only conceptof an electronic voting system that has been analyzed with respect to,and shown to comply with, the German legal requirements for use ofelectronic voting in the local elections of the state of Hesse.Furthermore, a feasibility analysis of various electronic voting systemsfor complex elections, showed that EasyVote seems to seems to be mostthe promising and adequate for complex elections with respect to theprinciple of secret and public nature of elections.

## Installation
Further instructions on setting up EasyVote Web can be found in the document Initialisierung von EasyVote.pdf.

## License
EasyVote is licensed under the Apache License, Version 2.0.

# Municipal Elections Karlsruhe
This version represents the election rules used in Baden-Württemberg. It is implemented for the municipal elections of Karlsruhe 2019.
This implementation is based on the php code of EasyVote of [SECUSO](https://github.com/SecUSo/EasyVote-web), the QR code is not visible to the user and hence, is not used for submitting votes. It should only be used to "practise" how to vote (cumulate and split) with the complex rules in Baden-Würrtemberg. Furthermore, it is only implemented in German and the existing source code was only changed minimally.
