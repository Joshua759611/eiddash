# s.regimen found, ask meaning
# s.receivedby not found 
# s.receivedby as received_by 
CREATE OR REPLACE VIEW old_samples_view AS
(
    SELECT s.id, s.originalid as original_sample_id,  
    s.AMRSlocation as amrs_location, s.provideridentifier as provider_identifier, s.orderno as order_no,
    s.sampletype as sample_type, s.receivedstatus, p.age,  s.pcrtype, p.prophylaxis as regimen, 
    m.prophylaxis as mother_prophylaxis, m.feeding, s.spots, s.comments, s.labcomment, s.parentid, 
    s.rejectedreason, s.reason_for_repeat, s.interpretation, s.result, s.worksheet as worksheet_id,
    s.hei_validation, s.enrollmentCCCno as enrollment_ccc_no, s.enrollmentstatus as enrollment_status, s.referredfromsite,
    s.otherreason, s.flag, s.run, s.repeatt, s.eqa, s.approvedby, s.approved2by as approvedby2, 
    s.datecollected, s.datetested, s.datemodified, s.dateapproved, s.dateapproved2,
    s.tat1, s.tat2, s.tat3, s.tat4, s.synched, s.datesynched, s.previous_positive, 
    m.lastvl as mother_last_result, m.age as mother_age,


    s.batchno as original_batch_id, s.highpriority, s.inputcomplete as input_complete, s.batchcomplete as 
    batch_complete, s.siteentry as site_entry, s.sentemail as sent_email, s.printedby, s.userid as user_id, 
    s.labtestedin as lab_id, s.facility as facility_id, 
    s.datedispatchedfromfacility, s.datereceived, s.datebatchprinted, s.datedispatched, 
    s.dateindividualresultprinted,  

    p.originalautoid as original_patient_id, s.patient, s.fullnames as patient_name, s.caregiverphoneno as 
    caregiver_phone, p.gender, m.entry_point,  s.dateinitiatedontreatment, p.dob,

    m.status as hiv_status, m.cccno as ccc_no

    FROM samples s
    LEFT JOIN patients p ON p.autoID=s.patientAUTOid
    LEFT JOIN mothers m ON m.id=p.mother

); 

CREATE OR REPLACE VIEW old_viralsamples_view AS
(
    SELECT s.id, s.originalid as original_sample_id, 
    s.AMRSlocation as amrs_location, s.provideridentifier as provider_identifier, s.orderno as order_no,
    s.vlrequestno as vl_test_request_no, s.receivedstatus, p.age, s.age2 as age_category, s.justification,
    s.otherjustification as other_justification, s.sampletype, s.prophylaxis, s.regimenline, p.pmtct,
    s.dilutionfactor, s.dilutiontype, s.comments, s.labcomment, s.parentid, s.rejectedreason, s.reason_for_repeat,
    s.rcategory, s.result, s.units, s.interpretation, s.worksheet as worksheet_id, s.flag, s.run, s.repeatt, s.approvedby,
    s.approved2by as approvedby2, s.datecollected, s.datetested, s.datemodified, s.dateapproved, s.dateapproved2, s.tat1,
    s.tat2, s.tat3, s.tat4, s.synched, s.datesynched, s.previous_nonsuppressed,

    s.batchno as original_batch_id, s.highpriority, s.inputcomplete as input_complete, s.batchcomplete as 
    batch_complete, s.siteentry as site_entry, s.sentemail as sent_email, s.printedby, s.userid as user_id, 
    s.labtestedin as lab_id, s.facility as facility_id, 
    s.datedispatchedfromfacility, s.datereceived, s.datebatchprinted, s.datedispatched, 
    s.dateindividualresultprinted, 

    p.originalautoid as original_patient_id, s.patient, s.fullnames as patient_name, s.caregiverphoneno as 
    caregiver_phone, p.gender, p.initiationdate as initiation_date, p.dob

    FROM viralsamples s
    LEFT JOIN viralpatients p ON p.AutoID=s.patientid

);

CREATE OR REPLACE VIEW old_worksheets_view AS
(
    SELECT id, type as machine_type, lab as lab_id, status as status_id,
    updatedby as uploadedby, reviewedby, review2by as reviewedby2, createdby, cancelledby,
    #sortedby, alliquotedby, bulkedby, runby,
    HIQCAPNo as hiqcap_no, Spekkitno as spekkit_no, Rackno as rack_no, Lotno as lot_no, samplepreplotno as
    sample_prep_lot_no, bulklysislotno as bulklysis_lot_no, controllotno as control_lot_no, calibratorlotno as
    calibrator_lot_no, amplificationkitlotno as amplification_kit_lot_no,

    negcontrolresult as neg_control_result, poscontrolresult as pos_control_result,
    negcontrolinterpretation as neg_control_interpretation, poscontrolinterpretation as pos_control_interpretation,
    cdcworksheetno, 
    kitexpirydate, sampleprepexpirydate, bulklysisexpirydate, controlexpirydate, calibratorexpirydate,
    amplificationexpirydate,
    datecut, datereviewed, review2date as datereviewed2, datecancelled, daterun, datecreated as created_at,
    dateupdated as dateuploaded,
    # datesynched,
    synched

    FROM worksheets_eid
);

CREATE OR REPLACE VIEW old_viralworksheets_view AS
(
    SELECT id, type as machine_type, lab as lab_id, status as status_id, calibration,
    updatedby as uploadedby, reviewedby, review2by as reviewedby2, createdby, cancelledby,
    #sortedby, alliquotedby, bulkedby, runby, 
    HIQCAPNo as hiqcap_no, Spekkitno as spekkit_no, Rackno as rack_no, Lotno as lot_no, samplepreplotno as
    sample_prep_lot_no, bulklysislotno as bulklysis_lot_no, controllotno as control_lot_no, calibratorlotno as
    calibrator_lot_no, amplificationkitlotno as amplification_kit_lot_no,

    negcontrolresult as neg_control_result, highposcontrolresult as highpos_control_result, 
    lowposcontrolresult as lowpos_control_result,

    negcontrolinterpretation as neg_control_interpretation, highposcontrolinterpretation as 
    highpos_control_interpretation, lowposcontrolinterpretation as lowpos_control_interpretation,

    negunits as neg_units, hpcunits as hpc_units, lpcunits as lpc_units,

    cdcworksheetno, 
    kitexpirydate, sampleprepexpirydate, bulklysisexpirydate, controlexpirydate, calibratorexpirydate,
    amplificationexpirydate,
    datecut, datereviewed, review2date as datereviewed2, datecancelled, daterun, datecreated as created_at,
    dateupdated as dateuploaded,
    # datesynched,
    synched

    FROM worksheets_vl
);
