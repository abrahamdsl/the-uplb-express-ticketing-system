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
//okay (tsss, not in conformance with HTTP, Informational to eh, di success?)
//19MAY2012-Consider this as "SUCCESS"
1000   USERNAME_EXISTS 						Obviously. ( how about vs 4202 ? )
1002   PASSWORD_CHANGE-SUCCESS				Obviously
1003   PAYMENT_PROCESS_OK					Succesfully proccessed payment
1004   BOOKING_ALREADY_PAID					Obviously.
1005   BOOKING_DEADLINE_LAPSED				The deadline for payment for the specified booking has passed and as such slots and seats are now forfeited.
1006   BOOKING_CONFIRM_CLEARED				The booking is cleared to undergo confirmation. 
1150   PAYPAL_IPN_MAIN_VALIDATE_OK          IPN Main method Validation SUCCESS
1151   PAYPAL_IPN_PLANB_VALIDATE_OK         IPN ALTERNATE Validation SUCCESS
1500   PAYMENT_MODE_EXISTS					Payment mode exists already
1501   PAYMENT_MODE_DELETED					The payment mode has been successfully deleted.
1502   PAYMENT_MODE_EDITED					The payment mode has been successfully edited.
1600   ROLES_EDITED                         User roles have been edited
2700   GEN_ACCOUNT_CHANGE_SAVED             The changes in your account have been saved
1850   SEATMAP_DELETED						The seat map has been successfully deleted.

//informational
2000   ALL_OKAY								obviously
2050   SAME_TC_NEWSCHED_NO_SLOT             There are no more slots available in the new showing time you have selected corresponding to the ticket class in your current booking.
2150   PAYPAL_IPN_INVALID                   Invalid IPN data. Maybe ipn function/URI was accessed via browser by some unscruplous person.
2200   ROLLBACK_DEADLINE_LAPSED             Pending changes to booking have been rolled back because deadline for payment lapsed.
2201   ROLLBACK_USER_DO                     User opted to cancel pending changes to booking.
2202   GUEST_NO_SHOW                        For logging purposes. <Supply guest UUID>.
2203   GUEST_SLOT_FREED                     For logging purposes. <Supply guest UUID and slot UUID>
2500   PAYMENT_MODE_ADDED					The payment mode has been successfully added.
2510   CONFIRM_PAYMENT_DELETION             Are you sure you want to delete this payment mode?
2515   DEFAULT_PAYMENT_DELETE_DENIED        By this system&aposs design, this payment mode is not designed to be removable. Edit my code if you want to.
2850   CONFIRM_SEATMAP_DELETION             Are you sure you want to delete this seat map?
//redirection
3100   REDIRECT_STAGE                       Redirect to stage <supply it>
3999   REDIRECT_CONFUSED					The server knows that you should be redirected but with your supplied information, it cannot determine which location you are to be redirected
//client error
4000   INFO_NEEDED							Information should be supplied by the user but is not found. <supply whenever possible>
4001   USERNAME_DOES-NOT-EXIST
4002   INFO_CRITICAL_NEEDED                 CRITICAL Information should be supplied by the user but is not found.
4003   AUTH_FAIL							Invalid credentials. Please try again. (The username and password combination is incorrect.)
4004   NOT_FOUND
4005   NO-PERMISSION-TO-BOOK-EXCEPT-HIMSELF The user specified that he is not bookable by other friends. Reserved for future use.
4006   INVALID_VALUE						The submitted data to the server is in the incorrect format
4007   CONFIRM_UNAUTHORIZED					You do not have permission to confirm a reservation for this event
4008   PAYMENT_MODE_UNAUTHORIZED			This payment mode is not allowed to be used for this event.
4030   EVENT_404							Event not found.
4031   SHOWING_TIME_404						Showing time not found
4032   BOOKING_404							The specified booking is not found in the system.
4100   ACCESS_DENIED_GEN                    You are not allowed to access this functionality/page.
4101   ACCESS_DENIED_NO_PERMIT              You need a specific permission to access this. Contact the system admin for details.
4102   BOOKING_NOT_OWNER					This booking is not under you and you do not have rights to make changes to it.
4102   STAGE_NOT_YET                        You are not allowed in this stage yet. Please accomplish an earlier form maybe.
4150   PAYMENT_MODE_DATA_404                Data for payment mode not found. <supply payment mode info>
4200   USER_ALREADY_EXISTS                  When signing up, user is already existing.
4201   RESERVED_WORD_USED                   A reserved word by the system is used, and not allowed (i.e., during signup and chosing a username).
4202   USERNAME_ALREADY_TAKEN               obviously.
4203   STUDENTNUM_ALREADY_TAKEN             obviously.
4204   EMPNUM_ALREADY_TAKEN                 obviously.
4103   ACCESS_DENIED_INVALID                Happens when a URI is accessed via address bar but only meant to be accessed via AJAX.
4800   COOKIE_ON_SERVER404					Cookie on server not found.
4998   INVALID_ENTRIES_SPECIFIED            Invalid entries specified.
4999   LOGIN_NEEDED                         You have to log-in first before you can access the feature requested
//server error
5050   INVALID_DATA_TC						Invalid data passed to ticket class selection.
5051   TC_404								Event Showing time marked as for sale but there is not any ticket class yet.
5100   TRANS_ID_404_ON_ROLLBACK				Cannot find transaction ID when rolling back lapsed change on booking
5101   BILLING_INFO_VANISHED				Billing info for this booking number suddenly became none? (I dunno if this will be used, but just in case)
5102   PAYMENT_CONFIRM_ERROR_UNKNOWN	    Unknown error occurred when confirming payment. Please try again.
5103   PAYMENT_RECEIVED_BUT_ERROR			Payment was received but there is an error in the transaction. (i.e., in PayPal, transaction was deemed to be fraudulent and fund was being held for review)
5104   PAYMENT_PROCESS_ERROR_UNKNOWN		Unknown error occurred when PROCESSING payment. Please try again.
5105   PAYMENT_MODE_USER_DECLINE            You declined to use the selected mode for payment. Please choose another payment mode. ( Server should supply which payment mode was chosen)
5110   PAYMENT_MODE_CRUCIAL_DATA_ERROR      A crucial piece of info needed to use the payment processing system is missing. 
5111   PAYMENT_MODE_CONFUSED_FINAL			At the confirmation page, we cannot determine what payment mode you have specified. Maybe the cookies were manipulated.
5149   PAYPAL_IPN_INITIATE_FAIL             Cannot open connection to PayPal for verification of IPN! (i.e., server disabled the PHP fsockopen() function )
5150   PAPYPAL_IPN_MAIN_VALIDATE_FAIL       IPN Main method Validation FAIL
5151   PAPYPAL_IPN_PLANB_VALIDATE_FAIL      IPN Main method Validation FAIL
5200   TRANS_ID_404_WHEN_ROLLBACK           FATAL ERROR: Cannot find transaction ID when rolling back lapsed change on booking.

//.. 5300 - 5350 EMAIL
5310   EMAIL_INFO_404						One or more necessary email info assumed to be in the database is not found. <specify whenever possible>
//.. ..5310-5320 EMAIL-SALES
5400   INTERNAL_FUNC_PARAM_GEN_ERR			Generic error due to an unexpected/invalid value of a parameter needed in an internal function, or no parameter is passed at all.


5500   PAYMENT_MODE_ADD_ERR					Something went wrong while adding the payment mode. It may have been not saved.
5505   PAYMENT_MODE_DELETE_ERR              Something went wrong while processing the deletion of the payment mode. It may have been not deleted. <br/><br/>Please try again.
5510   PAYMENT_MODE_EDIT_ERR                Something went wrong while processing the deletion of the payment mode. It may have been not deleted. <br/><br/>Please try again.
5600   ROLES_EDIT_ERR						Something went wrong while updating permissions. Your changes might not be saved.
5700   GEN_ACCOUNT_CHANGE_ERR				Something went wrong while saving changes to your account. Your changes might not be saved.
5850   SEATMAP_DELETE_ERR                   Something went wrong while processing the deletion of the seat map. It may have been not deleted. <br/><br/>Please try again.
5855   SEATMAP_DATACREATE_ERR				Something went wrong in actual seat data insertion to DB
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
BOOKING_CHANGE - UPDATED_BOOKING_DETAILS
BOOKING_NEW_PAYMENT - { BY_AUTHORIZED_AGENT | ONLINE_PAYMENT | AUTOMATIC_FREE }  
BOOKING_CHANGE_CONFIRM - { BY_AUTHORIZED_AGENT | ONLINE_PAYMENT | AUTOMATIC_FREE }  
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
Usernames
 - 'default' cannot be used as a username.

Payment costs
 - We do not have to refund if $amountDue is less than zero.

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