# database names and table names are case-sensitive on linux by default.
# However, I'm gonna stick with exact given names on assignment for this.

# physicians
create table `Physician` (
    physicianID     int ,
    name            varchar(40) not null ,
    position        varchar(40)
        check (
            position in ('Intern', 'Surgeon', 'Senior', 'Chief of Medicine', 'Resident', 'Psychiatrist')
        )   not null ,
    ssn             int , # this is just a nominal attribute, not an actual number.
    # so this will lose leading zeros of ssn, but we don't want that, anyway, I'm going with instructions.
    # international workers might not have an ssn yet, so null is allowed, and hence not unique

    primary key (physicianID)
);

# departments
create table `Department` (
    deptID          int ,
    name            varchar(40) check (
        name in ('General Medicine', 'Surgery', 'Psychiatry')
        ) not null ,
    headID          int , # there might not an head when defining department, hence we leave it null

    primary key (deptID),

    # department can live without a head, so when the head is removed, leave the department without a head
    foreign key (headID) references Physician(physicianID) on delete set null
);

# affiliated with -> pivot table for departments with physicians. (many to many)
create table `AffiliatedWith` (
    physicianID     int ,
    departmentID    int ,

    primary key (physicianID, departmentID),

    # if either department or physician removes, no point of having matching relation records, Hence, delete those
    foreign key (physicianID) references Physician(physicianID) on delete cascade ,
    foreign key (departmentID) references Department(deptID) on delete cascade
);

# procedure
create table `Procedure` (
    procID      int ,
    name        varchar(40) not null ,
    cost        real, # when defining new procedure, there might be not finalized price, so default null,
    # and on my local machine, my MYSQL configuraiton is REAL_AS_FLOAT is off. So type real is actually double on my machine.
    # But when running on your machine this might indicate as float. based on REAL_AS_FLOAT configuration

    primary key (procID)
);

# patients
create table `Patient` (
    patientID       int ,
    ssn             int , # some patients (newborns, foreigners) don't have an ssn, so null is allowed, and hence not unique
    name            varchar(40) not null ,
    address         varchar(100),
    dob             date,
    phone           varchar(16),
    insuranceNumber int , # patient might not have insurance, and family members can share one, so nullable and not unique
    primaryPhysID   int , #there might not be primary physician for some patient, hence I made this nullable

    primary key (patientID),

    # patient can live without prefered phycisian, so null is allowed in here
    foreign key (primaryPhysID) references Physician(physicianID) on delete set null
);

# nurses
create table `Nurse` (
    nurseID     int ,
    name        varchar(40) not null ,
    position varchar(40) check (
        position in ('Head Nurse', 'Nurse')
        ) not null ,
    ssn         int , # international workers might not have an ssn yet, so null is allowed, and hence not unique

    primary key (nurseID)
);

# medications (drugs)
create table `Medication` (
    medID   int,
    name    varchar(40) not null ,

    primary key (medID)
);

# prescribes
create table `Prescribes` (
    physicianID     int,
    patientID       int,
    medicationID    int,
    prescribedDate  date,
    dose            varchar(40),

    primary key (physicianID, patientID, medicationID, prescribedDate),

    # records belong to the patient -> cascade
    foreign key (patientID) references Patient(patientID) on delete cascade ,

    # records belong to staff/hospital -> stay
    foreign key (physicianID) references Physician(physicianID) on delete restrict ,
    foreign key (medicationID) references Medication(medID) on delete restrict
);

# rooms
create table `Room` (
    roomID      int,
    roomType    varchar(40) check (
        roomType in ('Single', 'Double')
        ) not null ,

    primary key (roomID)
);

# stays
create table `Stay` (
    stayID      int,
    patientID   int not null ,
    roomID      int not null ,
    startDate   date not null ,
    endDate     date, # when creating stay it is not finalized what is end date. So, i leave this with null

    primary key (stayID),

    # null endDate passes the check (unknown is not false)
    check (endDate > startDate),

    # when patient removes -> delete
    foreign key (patientID) references Patient(patientID) on delete cascade ,

    # when room removes -> assign patient to another room before remove
    foreign key (roomID) references Room(roomID) on delete restrict
);

# undergoes -> medical procedures
create table `Undergoes` (
    patientID       int not null ,
    procedureID     int not null ,
    stayID          int not null , # maybe the patient doesn't need to stay. But this is what we have given
    procDate        date not null ,
    physicianID     int not null ,
    nurseID         int not null ,

    primary key (patientID, procedureID, stayID, procDate),

    # all records belong to hospital -> stay,
    # so medical history can't be lost by deleting patient, stay, procedure or staff
    foreign key (patientID) references Patient(patientID) on delete restrict ,
    foreign key (stayID) references Stay(stayID) on delete restrict ,
    foreign key (procedureID) references `Procedure` (procID) on delete restrict ,
    foreign key (physicianID) references Physician(physicianID) on delete restrict ,
    foreign key (nurseID) references Nurse(nurseID) on delete restrict
);

# on call
create table `OnCall` (
    nurseID     int,
    startDate   date,
    endDate     date,

    primary key (nurseID, startDate, endDate),

    check (endDate > startDate),

    foreign key (nurseID) references Nurse(nurseID) on delete cascade
);

# appointments
create table `Appointment` (
    appID           int,
    patientID       int not null ,
    nurseID         int not null ,
    physicianID     int not null ,
    startDateTime   datetime not null ,
    endDateTime     datetime,

    primary key (appID),

    # null endDateTime passes the check (unknown is not false)
    check (endDateTime > startDateTime),

    # records belong to patient -> delete
    foreign key (patientID) references Patient(patientID) on delete cascade , # if patient removes, no more appointment

    # records belong to staff -> stays
    # assign different physician or nurse before remove
    foreign key (nurseID) references Nurse(nurseID) on delete restrict ,
    foreign key (physicianID) references Physician(physicianID) on delete restrict
);
