<?php
-----------------------------------------------
****
* Data representation
****
WIN5 vs XML
{
- WIN5
 It is a data representation based on the setup/configuration files used in Microsoft Windows operating system before Windows Vista.
 Entries are typically by this configuration:
 
 <quote>
 [section1]
 setting1=value1;
 setting2=value2;
 
 [section2]
 setting1=value1;
 setting2=value2;
 </quote>
 
 Sections are specified in square brackets while the entries under such section are specified via a descriptor,
 then an equal sign then the respective value.
 
 - XML
  Data representation that Extensible Markup Language (XML) is a markup language that defines a set of rules for encoding 
  documents in a format that is both human-readable and machine-readable. It is defined in the XML 1.0 Specification produced 
  by the W3C, and several other related specifications, all gratis open standards.

  [start]
   <?xml version="1.0"?>
   <quiz>
	<question>
		Who is the current president of PH?
    </question>
    <answer>
		Noynoy Aquino
    </answer>
   </quiz>
  [end]
 
 * These will be used throughout the app, like in database entries/columns and cookie/session data storage, so as to simplify/minimize
 space used by such. For example, in database tables - for payment modes, not all payment modes need a column for `txn_id` or `merchant_email`(Paypal)
 so adding a new column on the `payment_modes` table is just a nuisance. Instead, I just opted to add two new columns `data` and `datatype`
 so as to add custom data not common among the payment modes for a single payment mode whenever applicable.
 }
-----------------------------------------------
****
* RETURN CODES
****
{

NUM    STRING  								Message
//okay
1000   USERNAME-EXISTS 						Obviously. 
1002   PASSWORD_CHANGE-SUCCESS				Obviously
1003   PAYMENT_PROCESS_OK					Succesfully proccessed payment
1004   BOOKING_ALREADY_PAID					Obviously.
1005   BOOKING_DEADLINE_LAPSED				The deadline for payment for the specified booking has passed and as such slots and seats are now forfeited.
1006   BOOKING_CONFIRM_CLEARED				The booking is cleared to undergo confirmation. 
//client error
4000   INFO_NEEDED							Information should be supplied by the user but is not found.
4001   USERNAME_DOES-NOT-EXIST
4002
4003   AUTH_FAIL							The username and password combination is incorrect.
4004   NOT_FOUND
4005   NO-PERMISSION-TO-BOOK-EXCEPT-HIMSELF The user specified that he is not bookable by other friends. Reserved for future use.
4006   INVALID_VALUE						The submitted data to the server is in the incorrect format
4007   CONFIRM_UNAUTHORIZED					You do not have permission to confirm a reservation for this event			
4030   EVENT_404							Event not found.
4031   SHOWING_TIME_404						Showing time not found
4032   BOOKING_404							The specified booking is not found in the system.

//server error
5050   INVALID_DATA_TC						Invalid data passed to ticket class selection.
5051   TC_404								Event Showing time marked as for sale but there is not any ticket class yet.
5100   TRANS_ID_404_ON_ROLLBACK				Cannot find transaction ID when rolling back lapsed change on booking
5101   BILLING_INFO_VANISHED				Billing info for this booking number suddenly became none? (I dunno if this will be used, but just in case)
5102   PAYMENT_CONFIRM_ERROR_UNKNOWN	    Unknown error occurred when confirming payment. Please try again.
5103   PAYMENT_RECEIVED_BUT_ERROR			Payment was received but there is an error in the transaction. (i.e., in PayPal, transaction was deemed to be fraudulent and fund was being held for review)
5104   PAYMENT_PROCESS_ERROR_UNKNOWN		 Unknown error occurred when PROCESSING payment. Please try again.
}

-----------------
****
* OPERATIONS
****
see application/controllers/_constants.inc

-----------------
SOME OTHER REMARKS

BOOKING_CHANGE_LAPSE_FREED
BOOKING_CHANGE_SUCCESS_FREED
TICKET_CLASS_UPGRADE
BOOKING_CHANGE_CONFIRM - {} BY_AUTHORIZED_AGENT 
-----------------
****
* BOOKING STATUS
****
{
For table `booking_details` { `Status` & `Status2` }

done	staus			 	status2
ok		PAID				NULL				* All is well
		PAID				RESCHEDULED			* No fault on the client but is pending notification via manage booking that event is rescheduled.
ok		PAID				ROLLED-BACK			* Somewhat fault in the client - pending notification via manage booking that changes are reversed because of non-payment of dues in time.
		PAID				POSTPONED			* No fault on the client but is pending notification via manage booking that event is indefinitely postponed.
ok		BEING_BOOKED		NULL				* Obviously.
ok		PENDING-PAYMENT 	NEW					* New booking (i.e., not just a ticket class change/upgrade) that is pending payment
ok		PENDING-PAYMENT		MODIFY				* Old booking like ticket class change/upgrade that is pending payment
ok		CONSUMED			PARTIAL			    * Some guests under this booking already have entered the event
ok		CONSUMED			FULL				* All guest under this booking already have entered the event
		NOT-CONSUMED		NULL				* Made after "Straggle" mode is activated - no guests have entered the event/showed-up
ok		EXPIRED				NOT-YET-NOTIFIED	* For a new booking that is not paid on time - slots are forfeited and thus marked as expired for notification to client in manage booking.
ok		EXPIRED				FOR-DELETION		* When a booking is in "EXPIRED"-"NOT-YET-NOTIFIED" stage and client goes to manage booking section, the booking is set - for deletion in DB entries since client has already viewed such.
ok		LAPSED-HOLDING_TIME	NOT-YET-NOTIFIED	* User is booking, but lapsed on the allowed holding time for the ticket class being booked - pending notification to the user via manage booking section.
ok		LAPSED-HOLDING_TIME	FOR-DELETION		* After "LAPSED-HOLDING_TIME"-"NOT-YET-NOTIFIED" - since user viewed already then ok now for deletion.
		CANCELLED			EVENT-CANCELLED		* No fault on the client but is pending notification via manage booking that event is cancelled.
		}
-----------------------------
****
* FORFEITING OF BOOKING/SLOTS/SEATS
****
The database is changed only when the system is accessed. We would want that a running program on the server checks every minute
(or other intervals) if there are bookings not paid on time, but the author is not yet knowledgable on such. And he thinks
that it would be difficult to do such thing on a server on a public web hosting site. If the system is hosted on a dedicated server, or
the likes of UPLB SystemOne where you do have access to the terminal (SSH, whatever).. then that would be good and most if not all 
of the band-aid solutions you will be reading can be removed from the code).

The first solution that the system does is when another user is booking for such event and showing time, at that time the the system is checked if there are
'defaulted' bookings to be cleared of. So with this, when a payment-pending booking is made and deadline lapsed, the slots and
seats are still reserved to the that customer until some other customer purchases a ticket for the same event and showing time (in
which the 'defaulted bookings cleaner' will be run).

Now, sometime in April, the author made addition such that when a user logs in, the following is performed:
[1] 

(A) If the user has admin privileges, then all bookings..
(B) Else, then all bookings made only by that user..
  
  on an event that is upcoming (`Status` is 'CONFIGURED') is retrieved, then the cleanDefaultedBookings(..) of libraries\bookingmaintenance.php
  is called for each of them, so as to do what the function name implies.
 
[2]
Everytime a booking is to be confirmed via an Event Manager (i.e., payment thru cash on delivery), the EventID and ShowtimeID of that
booking concerned is too subject to the cleanDefaultedBookings(..) as stated in [1]. 

So, in conclusion:
* Defaulted bookings/slots/seats are only cleaned when
 (1) Another customer is booking ticket for the same event (and showing time)
 (2) The user logs on to the system
 (3) A booking for the same event (and showing time) is being confirmed.

* we have to waste execution time and bla bla because we do not have a running program on the server to 
automatically check every interval if there are defaulted bookings and act on it.

-----------------------------
****
* Entity Status
****

- Slots
 The column `Assigned_To_User` has a string of length at least 1 when the slot is assigned already.
 -----------------------------
****
* Factory Conventions 
****
Payment modes
 - Confirmation because of a "FREE EVENT" - UniqueID 0
 - Confirmation because of payment via "PAYPAL" - UniqueID 2
 
 
 Root User
 - DB Table: `user`
 - AccountNum: 0
 - Username: root
 - Password: unencrypted 'default' (hahaha, ano kaya equivalent neto sa whirlpool?)
 - Purpose ( This is just a dummy so as to adhere to 'foreign-key' idea sa database principles).
 
 Dummy payment entry
 - DB Table: `payments`
 - BookingNumber: 'XXXXX'
 - processedby: 0
 - Purpose ( This is just a dummy so as to adhere to 'foreign-key' idea sa database principles).